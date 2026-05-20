<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════════
        // TRIGGER 1: Auto-generate borrowing_number on insert
        // ══════════════════════════════════════════════════════
        DB::unprepared('DROP TRIGGER IF EXISTS trg_borrowings_before_insert');
        DB::unprepared('
            CREATE TRIGGER trg_borrowings_before_insert
            BEFORE INSERT ON borrowings
            FOR EACH ROW
            BEGIN
                DECLARE next_id INT;
                SET next_id = (SELECT COALESCE(MAX(id), 0) + 1 FROM borrowings);
                IF NEW.borrowing_number IS NULL OR NEW.borrowing_number = "" THEN
                    SET NEW.borrowing_number = CONCAT("BRW-", YEAR(NOW()), "-", LPAD(next_id, 5, "0"));
                END IF;
            END
        ');

        // ══════════════════════════════════════════════════════
        // TRIGGER 2: After borrow — decrement available_copies,
        //            increment borrowed_count on member
        // ══════════════════════════════════════════════════════
        DB::unprepared('DROP TRIGGER IF EXISTS trg_borrowings_after_insert');
        DB::unprepared('
            CREATE TRIGGER trg_borrowings_after_insert
            AFTER INSERT ON borrowings
            FOR EACH ROW
            BEGIN
                -- Decrease available copies on book
                UPDATE books
                SET available_copies = available_copies - 1
                WHERE id = NEW.book_id AND available_copies > 0;

                -- Mark the specific copy as borrowed
                IF NEW.book_copy_id IS NOT NULL THEN
                    UPDATE book_copies SET status = "borrowed" WHERE id = NEW.book_copy_id;
                END IF;

                -- Increment member borrowed count
                UPDATE members
                SET borrowed_count = borrowed_count + 1
                WHERE id = NEW.member_id;
            END
        ');

        // ══════════════════════════════════════════════════════
        // TRIGGER 3: After return — recalculate fine,
        //            restore available_copies, update member count
        // ══════════════════════════════════════════════════════
        DB::unprepared('DROP TRIGGER IF EXISTS trg_borrowings_after_update');
        DB::unprepared('
            CREATE TRIGGER trg_borrowings_after_update
            AFTER UPDATE ON borrowings
            FOR EACH ROW
            BEGIN
                -- When status changes to "returned"
                IF NEW.status = "returned" AND OLD.status != "returned" THEN

                    -- Restore available copy on book
                    UPDATE books
                    SET available_copies = available_copies + 1
                    WHERE id = NEW.book_id;

                    -- Mark book copy as available again
                    IF NEW.book_copy_id IS NOT NULL THEN
                        UPDATE book_copies SET status = "available" WHERE id = NEW.book_copy_id;
                    END IF;

                    -- Decrement member borrowed count (min 0)
                    UPDATE members
                    SET borrowed_count = GREATEST(borrowed_count - 1, 0)
                    WHERE id = NEW.member_id;

                    -- Notify next reservation in queue (set to available)
                    UPDATE reservations
                    SET status = "available",
                        notification_date = CURDATE(),
                        expiry_date = DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                    WHERE book_id = NEW.book_id
                      AND status = "pending"
                      AND queue_position = (
                          SELECT MIN(queue_position)
                          FROM reservations r2
                          WHERE r2.book_id = NEW.book_id AND r2.status = "pending"
                      );
                END IF;

                -- Auto-calculate fine when actual_return_date is set
                IF NEW.actual_return_date IS NOT NULL AND NEW.actual_return_date > NEW.due_date THEN
                    UPDATE borrowings
                    SET overdue_days = DATEDIFF(NEW.actual_return_date, NEW.due_date),
                        fine_amount  = DATEDIFF(NEW.actual_return_date, NEW.due_date) * 5.00
                    WHERE id = NEW.id;
                END IF;
            END
        ');

        // ══════════════════════════════════════════════════════
        // TRIGGER 4: Auto-generate membership_id on member insert
        // ══════════════════════════════════════════════════════
        DB::unprepared('DROP TRIGGER IF EXISTS trg_members_before_insert');
        DB::unprepared('
            CREATE TRIGGER trg_members_before_insert
            BEFORE INSERT ON members
            FOR EACH ROW
            BEGIN
                DECLARE next_id INT;
                SET next_id = (SELECT COALESCE(MAX(id), 0) + 1 FROM members);
                IF NEW.membership_id IS NULL OR NEW.membership_id = "" THEN
                    SET NEW.membership_id = CONCAT("MEM-", YEAR(NOW()), "-", LPAD(next_id, 4, "0"));
                END IF;
            END
        ');

        // ══════════════════════════════════════════════════════
        // TRIGGER 5: Auto-update total_copies & available_copies
        //            when a new book copy is inserted
        // ══════════════════════════════════════════════════════
        DB::unprepared('DROP TRIGGER IF EXISTS trg_book_copies_after_insert');
        DB::unprepared('
            CREATE TRIGGER trg_book_copies_after_insert
            AFTER INSERT ON book_copies
            FOR EACH ROW
            BEGIN
                UPDATE books
                SET total_copies     = (SELECT COUNT(*) FROM book_copies WHERE book_id = NEW.book_id),
                    available_copies = (SELECT COUNT(*) FROM book_copies WHERE book_id = NEW.book_id AND status = "available")
                WHERE id = NEW.book_id;
            END
        ');

        // ══════════════════════════════════════════════════════
        // TRIGGER 6: Auto-update counts when a copy status changes
        // ══════════════════════════════════════════════════════
        DB::unprepared('DROP TRIGGER IF EXISTS trg_book_copies_after_update');
        DB::unprepared('
            CREATE TRIGGER trg_book_copies_after_update
            AFTER UPDATE ON book_copies
            FOR EACH ROW
            BEGIN
                IF NEW.status != OLD.status THEN
                    UPDATE books
                    SET available_copies = (
                        SELECT COUNT(*) FROM book_copies
                        WHERE book_id = NEW.book_id AND status = "available"
                    )
                    WHERE id = NEW.book_id;
                END IF;
            END
        ');

        // ══════════════════════════════════════════════════════
        // TRIGGER 7: Auto-generate reservation_number on insert
        // ══════════════════════════════════════════════════════
        DB::unprepared('DROP TRIGGER IF EXISTS trg_reservations_before_insert');
        DB::unprepared('
            CREATE TRIGGER trg_reservations_before_insert
            BEFORE INSERT ON reservations
            FOR EACH ROW
            BEGIN
                DECLARE next_id INT;
                SET next_id = (SELECT COALESCE(MAX(id), 0) + 1 FROM reservations);
                IF NEW.reservation_number IS NULL OR NEW.reservation_number = "" THEN
                    SET NEW.reservation_number = CONCAT("RES-", YEAR(NOW()), "-", LPAD(next_id, 5, "0"));
                END IF;
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_borrowings_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_borrowings_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_borrowings_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_members_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_book_copies_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_book_copies_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_reservations_before_insert');
    }
};
