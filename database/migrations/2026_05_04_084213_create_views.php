<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════════
        // VIEW 1: v_active_borrowings
        // All currently borrowed (not returned) books with
        // member and book details in one place
        // ══════════════════════════════════════════════════════
        DB::unprepared('DROP VIEW IF EXISTS v_active_borrowings');
        DB::unprepared('
            CREATE VIEW v_active_borrowings AS
            SELECT
                b.id,
                b.borrowing_number,
                b.issue_date,
                b.due_date,
                b.status,
                b.fine_amount,
                b.fine_paid,
                b.notes,

                -- Member info
                m.id          AS member_id,
                m.name        AS member_name,
                m.membership_id,
                m.email       AS member_email,
                m.phone       AS member_phone,
                m.member_type,

                -- Book info
                bk.id         AS book_id,
                bk.title      AS book_title,
                bk.author     AS book_author,
                bk.isbn,

                -- Calculated days remaining (negative = overdue)
                DATEDIFF(b.due_date, CURDATE()) AS days_remaining

            FROM borrowings b
            JOIN members m  ON m.id  = b.member_id
            JOIN books   bk ON bk.id = b.book_id
            WHERE b.status IN ("borrowed", "overdue")
        ');

        // ══════════════════════════════════════════════════════
        // VIEW 2: v_overdue_borrowings
        // Only overdue books with live fine calculation
        // ══════════════════════════════════════════════════════
        DB::unprepared('DROP VIEW IF EXISTS v_overdue_borrowings');
        DB::unprepared('
            CREATE VIEW v_overdue_borrowings AS
            SELECT
                b.id,
                b.borrowing_number,
                b.issue_date,
                b.due_date,
                b.fine_paid,

                -- Member info
                m.id          AS member_id,
                m.name        AS member_name,
                m.membership_id,
                m.email       AS member_email,
                m.phone       AS member_phone,

                -- Book info
                bk.id         AS book_id,
                bk.title      AS book_title,
                bk.author     AS book_author,

                -- Live overdue calculation (not from stored column)
                DATEDIFF(CURDATE(), b.due_date)            AS days_overdue,
                DATEDIFF(CURDATE(), b.due_date) * 5.00     AS calculated_fine

            FROM borrowings b
            JOIN members m  ON m.id  = b.member_id
            JOIN books   bk ON bk.id = b.book_id
            WHERE b.status = "overdue"
               OR (b.status = "borrowed" AND b.due_date < CURDATE())
            ORDER BY b.due_date ASC
        ');

        // ══════════════════════════════════════════════════════
        // VIEW 3: v_member_summary
        // Members with live borrowing stats and fine totals
        // ══════════════════════════════════════════════════════
       DB::unprepared('DROP VIEW IF EXISTS v_member_summary');
DB::unprepared('
    CREATE VIEW v_member_summary AS
    SELECT
        m.id,
        m.membership_id,
        m.name,
        m.email,
        m.phone,
        m.member_type,
        m.status,
        m.membership_expiry_date,
        m.max_books,

        COUNT(b.id)                                                                           AS total_borrowed,
        SUM(b.status IN ("borrowed","overdue"))                                               AS currently_borrowed,
        SUM(b.status = "returned")                                                            AS total_returned,
        SUM(b.status = "overdue")                                                             AS total_overdue,
        COALESCE(SUM(b.fine_amount), 0)                                                       AS total_fines,
        COALESCE(SUM(CASE WHEN b.fine_paid = 0 AND b.fine_amount > 0 THEN b.fine_amount ELSE 0 END), 0) AS unpaid_fines,

        CASE
            WHEN m.membership_expiry_date < CURDATE() THEN "expired"
            WHEN m.membership_expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN "expiring_soon"
            ELSE "valid"
        END AS membership_status

    FROM members m
    LEFT JOIN borrowings b ON b.member_id = m.id
    WHERE m.deleted_at IS NULL
    GROUP BY
        m.id, m.membership_id, m.name, m.email, m.phone,
        m.member_type, m.status, m.membership_expiry_date, m.max_books
');
        // ══════════════════════════════════════════════════════
        // VIEW 4: v_book_availability
        // Books with live copy counts and borrow status
        // ══════════════════════════════════════════════════════
        DB::unprepared('DROP VIEW IF EXISTS v_book_availability');
DB::unprepared('
    CREATE VIEW v_book_availability AS
    SELECT
        bk.id,
        bk.title,
        bk.author,
        bk.isbn,
        bk.location,
        bk.is_active,
        cat.name AS category_name,

        COUNT(c.id)                 AS total_copies,
        SUM(c.status = "available") AS available_copies,
        SUM(c.status = "borrowed")  AS borrowed_copies,
        SUM(c.status = "damaged")   AS damaged_copies,

        (SELECT COUNT(*) FROM reservations r
         WHERE r.book_id = bk.id
         AND r.status IN ("pending","available")) AS reservation_count,

        CASE WHEN SUM(c.status = "available") > 0 THEN 1 ELSE 0 END AS is_available

    FROM books bk
    LEFT JOIN book_categories cat ON cat.id = bk.category_id
    LEFT JOIN book_copies c ON c.book_id = bk.id
    WHERE bk.deleted_at IS NULL
    GROUP BY
        bk.id, bk.title, bk.author, bk.isbn,
        bk.location, bk.is_active, cat.name
');

        // ══════════════════════════════════════════════════════
        // VIEW 5: v_daily_transactions
        // Everything issued or returned today
        // ══════════════════════════════════════════════════════
        DB::unprepared('DROP VIEW IF EXISTS v_daily_transactions');
        DB::unprepared('
            CREATE VIEW v_daily_transactions AS
            SELECT
                b.id,
                b.borrowing_number,
                b.issue_date,
                b.due_date,
                b.actual_return_date,
                b.status,
                b.fine_amount,
                b.fine_paid,
                b.updated_at,

                m.name        AS member_name,
                m.membership_id,

                bk.title      AS book_title,
                bk.author     AS book_author,

                CASE
                    WHEN DATE(b.actual_return_date) = CURDATE() THEN "returned"
                    WHEN DATE(b.issue_date)          = CURDATE() THEN "issued"
                END AS transaction_type

            FROM borrowings b
            JOIN members m  ON m.id  = b.member_id
            JOIN books   bk ON bk.id = b.book_id
            WHERE DATE(b.issue_date) = CURDATE()
               OR DATE(b.actual_return_date) = CURDATE()
            ORDER BY b.updated_at DESC
        ');

        // ══════════════════════════════════════════════════════
        // VIEW 6: v_monthly_stats
        // Per-month borrowing summary for charts/reports
        // ══════════════════════════════════════════════════════
        DB::unprepared('DROP VIEW IF EXISTS v_monthly_stats');
DB::unprepared('
    CREATE VIEW v_monthly_stats AS
    SELECT
        YEAR(issue_date)                    AS yr,
        MONTH(issue_date)                   AS mo,
        DATE_FORMAT(issue_date, "%b %Y")    AS month_label,
        COUNT(*)                            AS total_issued,
        SUM(status = "returned")            AS total_returned,
        SUM(status = "overdue")             AS total_overdue,
        COALESCE(SUM(fine_amount), 0)       AS total_fines
    FROM borrowings
    GROUP BY
        YEAR(issue_date),
        MONTH(issue_date),
        DATE_FORMAT(issue_date, "%b %Y")
    ORDER BY yr DESC, mo DESC
');
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_active_borrowings');
        DB::unprepared('DROP VIEW IF EXISTS v_overdue_borrowings');
        DB::unprepared('DROP VIEW IF EXISTS v_member_summary');
        DB::unprepared('DROP VIEW IF EXISTS v_book_availability');
        DB::unprepared('DROP VIEW IF EXISTS v_daily_transactions');
        DB::unprepared('DROP VIEW IF EXISTS v_monthly_stats');
    }
};
