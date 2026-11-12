CREATE DATABASE IF NOT EXISTS barber_booking -- Continue the existing schema statement or column definition.
CHARACTER SET utf8mb4 -- Continue the existing schema statement or column definition.
COLLATE utf8mb4_unicode_ci; -- Continue the existing schema statement or column definition.

USE barber_booking; -- Continue the existing schema statement or column definition.


-- ============================================
-- SHOP SETTINGS
-- ============================================

CREATE TABLE shop_settings ( -- Define this existing database table.

    id TINYINT UNSIGNED PRIMARY KEY, -- Continue the existing schema statement or column definition.

    shop_name VARCHAR(120) NOT NULL, -- Continue the existing schema statement or column definition.

    city VARCHAR(100) NOT NULL, -- Continue the existing schema statement or column definition.

    opening_time TIME NOT NULL -- Continue the existing schema statement or column definition.
        DEFAULT '10:00:00', -- Continue the existing schema statement or column definition.

    closing_time TIME NOT NULL -- Continue the existing schema statement or column definition.
        DEFAULT '20:00:00', -- Continue the existing schema statement or column definition.

    appointment_minutes -- Continue the existing schema statement or column definition.
        SMALLINT UNSIGNED NOT NULL -- Continue the existing schema statement or column definition.
        DEFAULT 60 -- Continue the existing schema statement or column definition.

); -- Continue the existing schema statement or column definition.


-- ============================================
-- BARBERS
-- ============================================

CREATE TABLE barbers ( -- Define this existing database table.

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- Continue the existing schema statement or column definition.

    name VARCHAR(100) NOT NULL, -- Continue the existing schema statement or column definition.

    active TINYINT(1) NOT NULL -- Continue the existing schema statement or column definition.
        DEFAULT 1 -- Continue the existing schema statement or column definition.

); -- Continue the existing schema statement or column definition.


-- ============================================
-- STAFF / ADMIN USERS
-- ============================================

CREATE TABLE staff_users ( -- Define this existing database table.

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- Continue the existing schema statement or column definition.

    username VARCHAR(50) NOT NULL UNIQUE, -- Continue the existing schema statement or column definition.

    password_hash VARCHAR(255) NOT NULL, -- Continue the existing schema statement or column definition.

    created_at TIMESTAMP -- Continue the existing schema statement or column definition.
        DEFAULT CURRENT_TIMESTAMP -- Continue the existing schema statement or column definition.

); -- Continue the existing schema statement or column definition.


-- ============================================
-- BOOKINGS
-- ============================================

CREATE TABLE bookings ( -- Define this existing database table.

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- Continue the existing schema statement or column definition.

    booking_date DATE NOT NULL, -- Continue the existing schema statement or column definition.

    booking_number INT UNSIGNED NOT NULL, -- Continue the existing schema statement or column definition.

    customer_name VARCHAR(120) NOT NULL,
    service_code VARCHAR(64) NULL, -- Snapshot the selected service code; older rows remain unknown.
    service_name VARCHAR(120) NULL, -- Retain the confirmed service name even if the catalogue changes.
    service_price_pence INT UNSIGNED NULL, -- Store the confirmed example GBP amount; payment remains at the shop. -- Continue the existing schema statement or column definition.

    contact_type ENUM( -- Continue the existing schema statement or column definition.
        'email' -- Continue the existing schema statement or column definition.
    ) NOT NULL, -- Continue the existing schema statement or column definition.

    contact_value VARCHAR(190) NOT NULL, -- Continue the existing schema statement or column definition.

    barber_id INT UNSIGNED NOT NULL, -- Continue the existing schema statement or column definition.

    appointment_time TIME NOT NULL, -- Continue the existing schema statement or column definition.

    status ENUM( -- Continue the existing schema statement or column definition.
        'confirmed', -- Continue the existing schema statement or column definition.
        'cancelled', -- Continue the existing schema statement or column definition.
        'completed' -- Continue the existing schema statement or column definition.
    ) NOT NULL -- Continue the existing schema statement or column definition.
        DEFAULT 'confirmed', -- Continue the existing schema statement or column definition.

    created_at TIMESTAMP -- Continue the existing schema statement or column definition.
        DEFAULT CURRENT_TIMESTAMP, -- Continue the existing schema statement or column definition.

    active_slot TINYINT GENERATED ALWAYS AS (CASE WHEN status = 'cancelled' THEN NULL ELSE 1 END) STORED, -- Continue the existing schema statement or column definition.

    updated_at TIMESTAMP -- Continue the existing schema statement or column definition.
        DEFAULT CURRENT_TIMESTAMP -- Continue the existing schema statement or column definition.
        ON UPDATE CURRENT_TIMESTAMP, -- Continue the existing schema statement or column definition.


    CONSTRAINT fk_booking_barber -- Declare the existing key, relationship or lookup index.

        FOREIGN KEY (barber_id) -- Declare the existing key, relationship or lookup index.

        REFERENCES barbers(id), -- Continue the existing schema statement or column definition.


    UNIQUE KEY unique_booking_number ( -- Declare the existing key, relationship or lookup index.

        booking_date, -- Continue the existing schema statement or column definition.

        booking_number -- Continue the existing schema statement or column definition.

    ), -- Continue the existing schema statement or column definition.


    UNIQUE KEY unique_barber_slot ( -- Declare the existing key, relationship or lookup index.

        booking_date, -- Continue the existing schema statement or column definition.

        appointment_time, -- Continue the existing schema statement or column definition.

        barber_id, -- Continue the existing schema statement or column definition.

        active_slot -- Continue the existing schema statement or column definition.

    ), -- Continue the existing schema statement or column definition.


    INDEX booking_date_status ( -- Declare the existing key, relationship or lookup index.

        booking_date, -- Continue the existing schema statement or column definition.

        status -- Continue the existing schema statement or column definition.

    ) -- Continue the existing schema statement or column definition.

); -- Continue the existing schema statement or column definition.


-- ============================================
-- DEFAULT SHOP SETTINGS
-- ============================================

INSERT INTO shop_settings ( -- Insert the existing initial records.

    id, -- Continue the existing schema statement or column definition.

    shop_name, -- Continue the existing schema statement or column definition.

    city, -- Continue the existing schema statement or column definition.

    opening_time, -- Continue the existing schema statement or column definition.

    closing_time, -- Continue the existing schema statement or column definition.

    appointment_minutes -- Continue the existing schema statement or column definition.

) -- Continue the existing schema statement or column definition.

VALUES ( -- Continue the existing schema statement or column definition.

    1, -- Continue the existing schema statement or column definition.

    'Local Barber Shop', -- Continue the existing schema statement or column definition.

    'London, UK', -- Continue the existing schema statement or column definition.

    '10:00:00', -- Continue the existing schema statement or column definition.

    '20:00:00', -- Continue the existing schema statement or column definition.

    60 -- Continue the existing schema statement or column definition.

); -- Continue the existing schema statement or column definition.


-- ============================================
-- TWO BARBERS
-- ============================================

INSERT INTO barbers ( -- Insert the existing initial records.

    name, -- Continue the existing schema statement or column definition.

    active -- Continue the existing schema statement or column definition.

) -- Continue the existing schema statement or column definition.

VALUES -- Continue the existing schema statement or column definition.

    ('Barber 1', 1), -- Continue the existing schema statement or column definition.

    ('Barber 2', 1); -- Continue the existing schema statement or column definition.
-- Staff availability and durable reminder attempts
CREATE TABLE IF NOT EXISTS barber_unavailability ( -- Define this existing database table.
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- Continue the existing schema statement or column definition.
    barber_id INT UNSIGNED NOT NULL, -- Continue the existing schema statement or column definition.
    unavailable_date DATE NOT NULL, -- Continue the existing schema statement or column definition.
    start_time TIME NOT NULL, -- Continue the existing schema statement or column definition.
    end_time TIME NOT NULL, -- Continue the existing schema statement or column definition.
    INDEX day_barber (unavailable_date, barber_id), -- Declare the existing key, relationship or lookup index.
    FOREIGN KEY (barber_id) REFERENCES barbers(id) -- Declare the existing key, relationship or lookup index.
); -- Continue the existing schema statement or column definition.
CREATE TABLE IF NOT EXISTS booking_reminders ( -- Define this existing database table.
    booking_id BIGINT UNSIGNED NOT NULL, -- Continue the existing schema statement or column definition.
    appointment_at DATETIME NOT NULL, -- Continue the existing schema statement or column definition.
    status VARCHAR(24) NOT NULL, -- Continue the existing schema statement or column definition.
    attempted_at DATETIME NOT NULL, -- Continue the existing schema statement or column definition.
    PRIMARY KEY (booking_id, appointment_at), -- Declare the existing key, relationship or lookup index.
    FOREIGN KEY (booking_id) REFERENCES bookings(id) -- Declare the existing key, relationship or lookup index.
); -- Continue the existing schema statement or column definition.
