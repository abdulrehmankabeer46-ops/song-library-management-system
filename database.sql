--  Song Library Management System - Database Schema
--  Course: Advanced Web Development / Full Stack Development
 

CREATE DATABASE IF NOT EXISTS song_library_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE song_library_db;

-- -------------------------------------------------------
-- Table: genres
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS genres (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Table: artists
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS artists (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(200) NOT NULL,
    country VARCHAR(100)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Table: songs  (main entity)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS songs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255)   NOT NULL,
    artist_id    INT            NOT NULL,
    genre_id     INT            NOT NULL,
    album        VARCHAR(255),
    duration_sec INT            NOT NULL COMMENT 'Duration in seconds',
    year         YEAR           NOT NULL,
    rating       TINYINT        DEFAULT 3 CHECK (rating BETWEEN 1 AND 5),
    play_count   INT            DEFAULT 0,
    created_at   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_song_artist FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE,
    CONSTRAINT fk_song_genre  FOREIGN KEY (genre_id)  REFERENCES genres(id)  ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Seed Data
-- -------------------------------------------------------
INSERT INTO genres (name) VALUES
    ('Pop'), ('Rock'), ('Hip-Hop'), ('R&B'), ('Jazz'),
    ('Classical'), ('Electronic'), ('Country'), ('Indie'), ('Metal');

INSERT INTO artists (name, country) VALUES
    ('The Weeknd',       'Canada'),
    ('Taylor Swift',     'USA'),
    ('Ed Sheeran',       'UK'),
    ('Eminem',           'USA'),
    ('Billie Eilish',    'USA'),
    ('Dua Lipa',         'UK'),
    ('Arctic Monkeys',   'UK'),
    ('Kendrick Lamar',   'USA'),
    ('Adele',            'UK'),
    ('Burna Boy',        'Nigeria');

INSERT INTO songs (title, artist_id, genre_id, album, duration_sec, year, rating, play_count) VALUES
    ('Blinding Lights',      1, 1,  'After Hours',            200, 2019, 5, 4200),
    ('Starboy',              1, 1,  'Starboy',                230, 2016, 4, 3100),
    ('Anti-Hero',            2, 1,  'Midnights',              200, 2022, 5, 5800),
    ('Shake It Off',         2, 1,  '1989',                   219, 2014, 4, 4500),
    ('Shape of You',         3, 1,  'Divide',                 234, 2017, 4, 6200),
    ('Perfect',              3, 1,  'Divide',                 263, 2017, 5, 5100),
    ('Lose Yourself',        4, 3,  '8 Mile',                 326, 2002, 5, 7800),
    ('Rap God',              4, 3,  'The Marshall Mathers LP2',363, 2013, 5, 6700),
    ('bad guy',              5, 1,  'WHEN WE ALL FALL ASLEEP',174, 2019, 4, 4900),
    ('Happier Than Ever',    5, 1,  'Happier Than Ever',      294, 2021, 4, 3200),
    ('Levitating',           6, 1,  'Future Nostalgia',       203, 2020, 4, 5500),
    ('Don''t Start Now',     6, 1,  'Future Nostalgia',       183, 2019, 4, 4800),
    ('505',                  7, 2,  'Favourite Worst Nightmare',253,2007, 5, 4100),
    ('R U Mine?',            7, 2,  'AM',                     200, 2013, 5, 3700),
    ('HUMBLE.',              8, 3,  'DAMN.',                  177, 2017, 5, 6900),
    ('Money Trees',          8, 3,  'good kid, m.A.A.d city', 386, 2012, 5, 5300),
    ('Rolling in the Deep',  9, 1,  '21',                     228, 2010, 5, 8100),
    ('Hello',                9, 1,  '25',                     295, 2015, 5, 7600),
    ('Last Last',           10, 3,  'Love, Damini',           198, 2022, 4, 2900),
    ('Ye',                  10, 1,  'African Giant',          253, 2019, 4, 3300);

-- -------------------------------------------------------
-- Useful view for the app
-- -------------------------------------------------------
CREATE OR REPLACE VIEW v_songs AS
    SELECT
        s.id,
        s.title,
        a.name              AS artist,
        g.name              AS genre,
        s.album,
        s.duration_sec,
        CONCAT(
            LPAD(FLOOR(s.duration_sec / 60), 2, '0'), ':',
            LPAD(MOD(s.duration_sec, 60),    2, '0')
        )                   AS duration_fmt,
        s.year,
        s.rating,
        s.play_count,
        s.created_at,
        s.updated_at
    FROM songs s
    JOIN artists a ON a.id = s.artist_id
    JOIN genres  g ON g.id = s.genre_id;
