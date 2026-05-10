-- =============================================
-- Seed Data: Sample products for the eShop
-- These populate the store on first run
-- =============================================

USE eshop;

INSERT INTO products (name, description, price, image_path, stock) VALUES
('Wireless Headphones', 'Premium noise-cancelling Bluetooth headphones with 30-hour battery life and comfortable over-ear design.', 79.99, 'images/headphones.jpg', 25),
('Smart Watch', 'Fitness tracker with heart rate monitor, GPS, and 7-day battery. Water resistant to 50m.', 149.99, 'images/smartwatch.jpg', 15),
('Laptop Stand', 'Ergonomic aluminium laptop stand with adjustable height. Compatible with all laptops up to 17 inches.', 34.99, 'images/laptop-stand.jpg', 40),
('Mechanical Keyboard', 'RGB mechanical keyboard with Cherry MX switches, USB-C connection, and programmable keys.', 89.99, 'images/keyboard.jpg', 30),
('USB-C Hub', '7-in-1 USB-C hub with HDMI 4K, SD card reader, 3x USB 3.0, and 100W power delivery.', 44.99, 'images/usb-hub.jpg', 50),
('Webcam HD', '1080p webcam with auto-focus, built-in microphone, and privacy shutter. Plug and play.', 59.99, 'images/webcam.jpg', 20);
