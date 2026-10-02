-- Review existing indexes before adding duplicates.
ALTER TABLE employee_assignments ADD INDEX idx_assign_employee_status_due (employee_id,status,due_at);
ALTER TABLE attendance ADD INDEX idx_attendance_employee_date_status (employee_id,attendance_date,status);
ALTER TABLE orders ADD INDEX idx_orders_user_created (user_id,created_at);
ALTER TABLE order_items ADD INDEX idx_order_items_order_product (order_id,product_id);
ALTER TABLE products ADD INDEX idx_products_active_category_created (status,category,created_at);
ALTER TABLE message_deliveries ADD INDEX idx_message_deliveries_user_channel_status (user_id,channel,status);
