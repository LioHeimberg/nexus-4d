-- Nexus 4D Dummy Data Generator
-- Version: 1.0.0

-- Insert Admin user
INSERT INTO `users` (`email`, `password_hash`, `role`, `first_name`, `last_name`) 
VALUES ('admin@nexus4d.ch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin', 'User');

-- Insert Boss user
INSERT INTO `users` (`email`, `password_hash`, `role`, `first_name`, `last_name`) 
VALUES ('boss@nexus4d.ch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'boss', 'Boss', 'Team');

-- Insert 10 JugendTeam members
INSERT INTO `users` (`email`, `password_hash`, `role`, `first_name`, `last_name`) 
VALUES 
('member1@nexus4d.ch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'Lukas', 'Meyer'),
('member2@nexus4d.ch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'Sophie', 'Schmidt'),
('member3@nexus4d.ch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'Jonas', 'Wagner'),
('member4@nexus4d.ch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'Emma', 'Fischer'),
('member5@nexus4d.ch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'Max', 'Müller'),
('member6@nexus4d.ch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'Anna', 'Becker'),
('member7@nexus4d.ch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'David', 'Schneider'),
('member8@nexus4d.ch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'Maria', 'Walter'),
('member9@nexus4d.ch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'Tom', 'Kraus'),
('member10@nexus4d.ch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'Lisa', 'Hofmann');

-- Insert 2 Rümli bars
INSERT INTO `bars` (`name`, `location`, `boss_id`) 
VALUES 
('Rümli Wünnewil', 'Wünnewil, Dorfplatz 5', 2),
('Rümli Schmitten', 'Schmitten, Hauptstrasse 12', 2);

-- Insert 5 events
INSERT INTO `events` (`title`, `description`, `event_date`, `location`, `boss_id`) 
VALUES 
('Sommerfest 2026', 'Jährliches Sommerfest mit Spiel und Spaß für alle Jugendlichen', '2026-07-15 14:00:00', 'Wünnewil, Parkanlage', 2),
('Stadtfest Schmitten', 'Mitwirkung am Stadtfest mit unserem Stand', '2026-08-02 10:00:00', 'Schmitten, Marktplatz', 2),
('Wochenende im Wald', 'Zeltlager mit Naturerfahrung und Teambuilding', '2026-09-12 16:00:00', 'Ueberstorf, Waldgebiet', 2),
('Rümli Open Day', 'Einladung zur Rümli-Einweihung mit Aktionen', '2026-06-20 15:00:00', 'Wünnewil, Dorfplatz 5', 2),
('Weihnachtsmarkt', 'Hilfe beim Weihnachtsmarkt mit Getränkestand', '2026-12-05 13:00:00', 'Schmitten, Marktplatz', 2);

-- Insert 3 posts
INSERT INTO `posts` (`title`, `content`, `boss_id`) 
VALUES 
('Neues Projekt: Jugendbeteiligung', 'Ab nächster Woche starten wir ein neues Projekt zur Jugendbeteiligung an der Gestaltung der Jugendräume. Wir suchen engagierte Mitglieder, die Ideen einbringen möchten.', 2),
('Sommerferienprogramm', 'Die Sommerferien stehen vor der Tür! Wir haben ein spannendes Programm mit Ausflügen, Workshops und Spielen geplant. Kommt vorbei!', 2),
('Zusammenarbeit mit Schulen', 'Wir arbeiten ab Herbst mit den lokalen Schulen zusammen, um noch mehr Jugendliche zu erreichen und zu unterstützen.', 2);

-- Insert reviews for members from guests
INSERT INTO `reviews` (`reviewer_type`, `reviewer_id`, `target_user_id`, `event_id`, `bar_id`, `rating_friendly`, `rating_professional`, `rating_overall`, `comment`) 
VALUES 
('guest', NULL, 3, NULL, 1, 5, 4, 5, 'Sehr freundlich und hat mir ein tolles Getränk zubereitet!'),
('guest', NULL, 3, NULL, 1, 4, 5, 5, 'Hatte einen super Tag dank Lukas!'),
('guest', NULL, 4, NULL, 1, 5, 5, 5, 'Die beste Betreuung überhaupt!'),
('guest', NULL, 5, NULL, 2, 4, 4, 4, 'War sehr hilfsbereit beim Stadtfest.'),
('guest', NULL, 6, NULL, 2, 5, 5, 5, 'Habe mich sehr wohlgefühlt mit Anna am Stand!'),
('guest', NULL, 7, NULL, 2, 3, 4, 4, 'War etwas zurückhaltend, aber professionell.'),
('guest', NULL, 8, NULL, 1, 5, 5, 5, 'Marias Lachen hat den ganzen Tag versüßt!'),
('guest', NULL, 9, NULL, 1, 4, 5, 5, 'Hatte eine super Beratung von Tom.'),
('guest', NULL, 10, NULL, 1, 5, 4, 5, 'Lisa hat meinen Tag gerettet mit ihrem Lächeln!'),
('guest', NULL, 3, 1, NULL, 5, 5, 5, 'Lukas hat das Sommerfest perfekt organisiert!'),
('guest', NULL, 4, 2, NULL, 4, 5, 5, 'Sophie war eine große Hilfe am Stadtfeststand!'),
('guest', NULL, 6, 3, NULL, 5, 4, 5, 'Anna hat uns beim Zeltlager super betreut!');

-- Insert reviews from other members
INSERT INTO `reviews` (`reviewer_type`, `reviewer_id`, `target_user_id`, `event_id`, `bar_id`, `rating_friendly`, `rating_professional`, `rating_overall`, `comment`) 
VALUES 
('member', 5, 3, NULL, NULL, 5, 5, 5, 'Lukas ist immer super hilfsbereit!'),
('member', 6, 4, NULL, NULL, 4, 4, 4, 'Sophie ist sehr zuverlässig, aber etwas schüchtern.'),
('member', 7, 5, NULL, NULL, 5, 5, 5, 'Max ist ein echter Teamplayer!'),
('member', 8, 6, NULL, NULL, 4, 5, 5, 'Anna hat mich sehr unterstützt!'),
('member', 9, 7, NULL, NULL, 4, 4, 4, 'David ist professionell, aber könnte etwas mehr lachen.'),
('member', 10, 8, NULL, NULL, 5, 5, 5, 'Maria ist die beste!');

-- Insert event participation
INSERT INTO `event_participation` (`event_id`, `member_id`, `status`) 
VALUES 
(1, 3, 'yes'), (1, 4, 'yes'), (1, 5, 'maybe'), (1, 6, 'yes'), (1, 7, 'no'), (1, 8, 'yes'), (1, 9, 'yes'), (1, 10, 'maybe'),
(2, 3, 'maybe'), (2, 4, 'yes'), (2, 5, 'yes'), (2, 6, 'yes'), (2, 7, 'yes'), (2, 8, 'yes'), (2, 9, 'no'), (2, 10, 'yes'),
(3, 3, 'no'), (3, 4, 'yes'), (3, 5, 'maybe'), (3, 6, 'yes'), (3, 7, 'yes'), (3, 8, 'yes'), (3, 9, 'yes'), (3, 10, 'yes'),
(4, 3, 'yes'), (4, 4, 'yes'), (4, 5, 'yes'), (4, 6, 'yes'), (4, 7, 'yes'), (4, 8, 'yes'), (4, 9, 'yes'), (4, 10, 'yes'),
(5, 3, 'yes'), (5, 4, 'yes'), (5, 5, 'yes'), (5, 6, 'yes'), (5, 7, 'yes'), (5, 8, 'yes'), (5, 9, 'yes'), (5, 10, 'yes');
