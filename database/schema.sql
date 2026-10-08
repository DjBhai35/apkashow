-- CinemaVault / CineStream Premium Cinematic Movie Platform Database Schema
-- Production-Ready MySQL 5.7+ / 8.0+ Schema with Initial Seed Data

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table structure for `settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key_name` VARCHAR(100) NOT NULL UNIQUE,
  `key_value` LONGTEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `users` (Admin Authentication)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('admin', 'editor') NOT NULL DEFAULT 'admin',
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `icon` VARCHAR(50) NULL DEFAULT 'bi-film',
  `meta_title` VARCHAR(150) NULL,
  `meta_description` VARCHAR(255) NULL,
  `display_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_slug` (`slug`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `movies`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `movies`;
CREATE TABLE `movies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `poster` VARCHAR(255) NOT NULL,
  `banner` VARCHAR(255) NULL,
  `short_description` VARCHAR(500) NOT NULL,
  `description` LONGTEXT NOT NULL,
  `category_id` INT NOT NULL,
  `genre` VARCHAR(150) NOT NULL,
  `language` VARCHAR(80) NOT NULL DEFAULT 'English',
  `release_year` INT NOT NULL,
  `duration` VARCHAR(50) NOT NULL,
  `rating` DECIMAL(3,1) DEFAULT 8.5,
  `tags` VARCHAR(255) NULL,
  `trailer_url` VARCHAR(255) NULL,
  `watch_url` VARCHAR(255) NULL,
  `download_url` VARCHAR(255) NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_popular` TINYINT(1) NOT NULL DEFAULT 0,
  `views_count` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `meta_title` VARCHAR(180) NULL,
  `meta_description` VARCHAR(255) NULL,
  `meta_keywords` VARCHAR(255) NULL,
  `canonical_url` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_slug` (`slug`),
  INDEX `idx_cat` (`category_id`),
  INDEX `idx_featured` (`is_featured`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `fk_movies_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `featured_banners`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `featured_banners`;
CREATE TABLE `featured_banners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `subtitle` VARCHAR(255) NULL,
  `badge` VARCHAR(50) NULL DEFAULT 'FEATURED PREMIERE',
  `banner_image` VARCHAR(255) NOT NULL,
  `movie_id` INT NULL,
  `target_url` VARCHAR(255) NULL,
  `button_text` VARCHAR(50) DEFAULT 'Watch Now',
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_banner_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `contact_messages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed Data: Default Admin User (Password: admin123)
-- Hash generated via password_hash('admin123', PASSWORD_BCRYPT)
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `status`) VALUES
(1, 'admin', 'admin@cinemavault.com', '$2y$10$wE1Vp2cI7Z1d3s6fW9hSzeO9pQfFjJ7B7z4oYhQxV0d6L1yF2sAOm', 'CinemaVault Administrator', 'admin', 1);

-- --------------------------------------------------------
-- Seed Data: Site Settings & WhatsApp Configuration
-- --------------------------------------------------------
INSERT INTO `settings` (`key_name`, `key_value`) VALUES
('site_name', 'CinemaVault Elite'),
('site_tagline', 'Stream Premium Motivational, Billionaire & Cinematic Cinema'),
('site_logo_text', 'CINEMA<span class=\"text-gradient\">VAULT</span>'),
('whatsapp_number', '+1234567890'),
('whatsapp_message', 'Hello CinemaVault! I would like to inquire about premium movies and VIP access.'),
('contact_email', 'contact@cinemavault.com'),
('contact_phone', '+1 (800) 555-VAULT'),
('contact_address', '100 Hollywood Blvd, Suite 880, Los Angeles, CA 90028'),
('about_hero_title', 'The World’s Premier Cinematic Hub for Visionaries'),
('about_hero_subtitle', 'Fueling ambition, financial mastery, and high-performance mindsets through elite storytelling and masterclass cinema.'),
('about_content', '<p class=\"lead text-light\">Welcome to <strong>CinemaVault</strong>, an exclusive streaming and film archive crafted for ambitious minds, entrepreneurs, traders, and cinematic aficionados.</p><p>We believe cinema is more than entertainment—it is a blueprint of psychology, resilience, capital mastery, and human drive. From high-stakes Wall Street boardrooms and global forex arenas to historic biographies of self-made titans, CinemaVault curates stories that expand horizons.</p><h4 class=\"text-white mt-4\">Our Mission</h4><p>To deliver a lag-free, ultra-cinematic viewing experience with curated masterworks in 4K HDR aesthetics, complete metadata, legal streaming links, and inspirational narratives.</p>'),
('header_ad_code', '<!-- Admin Header Advertisement Slot (728x90 or Responsive) --><div class=\"ad-banner-placeholder text-center p-2 rounded\"><small class=\"text-muted d-block text-uppercase letter-spacing-1\"><i class=\"bi bi-badge-ad me-1\"></i>Sponsored Partner</small><a href=\"#\" class=\"text-info text-decoration-none fw-semibold\">Level Up Your Trading with Global Forex Academy &bull; Claim $100 Welcome Bonus</a></div>'),
('footer_ad_code', '<!-- Admin Footer Ad Placement --><div class=\"ad-banner-placeholder text-center p-2 rounded\"><small class=\"text-muted d-block text-uppercase letter-spacing-1\"><i class=\"bi bi-badge-ad me-1\"></i>Featured Sponsor</small><span class=\"text-light\">Automate Your Wealth Portfolio &bull; Join Titan Capital Mastermind</span></div>'),
('meta_title_default', 'CinemaVault | Premium Movies, Billionaire Mindset & Cinema Hub'),
('meta_description_default', 'Explore curated high-definition cinema, motivational billionaire stories, Wall Street dramas, forex mastery, and blockbuster movies on CinemaVault.'),
('meta_keywords_default', 'movies, billionaire movies, forex cinema, mindset films, motivational movies, bollywood, hollywood, streaming, cinema vault'),
('footer_copyright', '&copy; 2026 CinemaVault Elite Media. All rights reserved. For entertainment and educational analysis only.');

-- --------------------------------------------------------
-- Seed Data: Dynamic Categories Requested by User
-- --------------------------------------------------------
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `meta_title`, `meta_description`, `display_order`, `status`) VALUES
(1, 'Millionaire', 'millionaire', 'High-stakes stories of wealth creation, ambition, startup journeys, and self-made fortunes.', 'bi-currency-dollar', 'Millionaire Movies & Wealth Biographies | CinemaVault', 'Watch gripping movies about millionaires, startups, venture capital, and relentless ambition.', 1, 1),
(2, 'Billionaire', 'billionaire', 'Dynastic wealth, empire building, corporate power plays, and ultra-high-net-worth titans.', 'bi-gem', 'Billionaire Movies & Empire Stories | CinemaVault', 'Cinematic blockbusters focusing on tech tycoons, empire builders, and billionaire legacies.', 2, 1),
(3, 'Mindset', 'mindset', 'Mental toughness, stoicism, psychological endurance, and unshakeable discipline.', 'bi-lightning-charge', 'Mindset & Mental Toughness Movies | CinemaVault', 'Films that redefine human limits, focus, peak mental discipline, and psychological mastery.', 3, 1),
(4, 'Business', 'business', 'Wall Street masterminds, boardroom warfare, corporate takeovers, and visionary ventures.', 'bi-briefcase', 'Business & Entrepreneur Movies | CinemaVault', 'Top-rated entrepreneurship, corporate warfare, and business strategy movies.', 4, 1),
(5, 'Forex', 'forex', 'Global currency markets, fast-paced day trading, algorithms, and high-frequency finance.', 'bi-graph-up-arrow', 'Forex Trading & Wall Street Films | CinemaVault', 'Heart-racing movies exploring currency trading, market crashes, and financial speculation.', 5, 1),
(6, 'Motivational', 'motivational', 'Inspiring real-life triumphs, overcoming impossible odds, and champion transformations.', 'bi-fire', 'Motivational & Inspirational Movies | CinemaVault', 'Get inspired with the most powerful motivational films and comeback stories in cinema history.', 6, 1),
(7, 'Action', 'action', 'Heart-pounding adrenaline, masterclass choreography, tactical warfare, and thrillers.', 'bi-bullseye', 'Action Blockbusters & Thrillers | CinemaVault', 'High-octane action movies featuring masterclass stunts, suspense, and heroic spectacles.', 7, 1),
(8, 'Thriller', 'thriller', 'Nail-biting suspense, mind-bending twists, intelligence agencies, and crime investigations.', 'bi-eye', 'Thriller & Mystery Cinema | CinemaVault', 'Psychological thrillers, gripping mysteries, and pulse-pounding plot twists.', 8, 1),
(9, 'Bollywood', 'bollywood', 'Vibrant Indian cinema, legendary musical spectacles, dramatic epics, and superstars.', 'bi-stars', 'Bollywood Masterpieces & Indian Cinema | CinemaVault', 'The finest Hindi and Indian cinema blockbusters, emotional epics, and superstar hits.', 9, 1),
(10, 'Hindi', 'hindi', 'Critically acclaimed Hindi dubbed & original cinematic classics with pristine audio.', 'bi-translate', 'Hindi Dubbed & Regional Cinema | CinemaVault', 'Top Hindi original and dubbed movies featuring crisp high-definition quality.', 10, 1),
(11, 'Comedy', 'comedy', 'Laugh-out-loud humor, witty satire, comedic heist masterpieces, and light-hearted escapes.', 'bi-emoji-smile', 'Comedy Movies & Feel-Good Cinema | CinemaVault', 'Lighten the mood with timeless comedies, clever satires, and buddy adventures.', 11, 1),
(12, 'Romantic', 'romantic', 'Deep passion, eternal romance, heartwarming connections, and sweeping love stories.', 'bi-heart', 'Romantic Dramas & Love Stories | CinemaVault', 'Emotional love stories, romantic dramas, and captivating screen chemistry.', 12, 1);

-- --------------------------------------------------------
-- Seed Data: Curated High-Production Movies
-- --------------------------------------------------------
INSERT INTO `movies` (`id`, `title`, `slug`, `poster`, `banner`, `short_description`, `description`, `category_id`, `genre`, `language`, `release_year`, `duration`, `rating`, `tags`, `trailer_url`, `watch_url`, `download_url`, `is_featured`, `is_popular`, `views_count`, `status`, `meta_title`, `meta_description`, `meta_keywords`, `canonical_url`) VALUES
(1, 'The Wolf of Wall Street', 'the-wolf-of-wall-street', 'https://images.unsplash.com/photo-1590283603385-17ffb3a7f29f?auto=format&fit=crop&w=600&q=80', 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?auto=format&fit=crop&w=1600&q=80', 'The true story of Jordan Belfort, rising to colossal wealth as a stockbroker before facing the SEC and federal authorities.', 'Based on the true story of Jordan Belfort, from his rise to a wealthy stockbroker living the high life to his fall involving crime, corruption and the federal government. Directed by Martin Scorsese, starring Leonardo DiCaprio in a career-defining performance capturing the adrenaline, madness, and ruthless drive of 1990s Wall Street brokerage firms.', 1, 'Biography, Crime, Drama', 'English', 2013, '180 min', 8.2, 'wall street, stocks, millionaire, jordan belfort, finance', 'https://www.youtube.com/embed/iszwuX1AK6A', 'https://www.youtube.com/watch?v=iszwuX1AK6A', 'https://archive.org/download/sample-cinemavault/wolf-wall-street.mp4', 1, 1, 14200, 1, 'The Wolf of Wall Street (2013) - Full Movie Stream & Analysis | CinemaVault', 'Watch The Wolf of Wall Street. Discover the high-stakes journey of Jordan Belfort on Wall Street.', 'wolf of wall street, millionaire movies, finance cinema', ''),

(2, 'The Social Network', 'the-social-network', 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=600&q=80', 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1600&q=80', 'As Harvard student Mark Zuckerberg creates the social networking site that would become Facebook, he faces lawsuits and personal rifts.', 'In the autumn of 2003, Harvard sophomore and computer programming genius Mark Zuckerberg sits at his computer and begins working on a new idea. In a flurry of blogging and programming, what begins in his dorm room soon becomes a global social network and a revolution in communication. A monumental study of raw ambition, technological genius, and the billionaire mindset.', 2, 'Biography, Drama', 'English', 2010, '120 min', 8.1, 'billionaire, tech, startup, mark zuckerberg, silicon valley', 'https://www.youtube.com/embed/lB95KLmpLR4', 'https://www.youtube.com/watch?v=lB95KLmpLR4', 'https://archive.org/download/sample-cinemavault/social-network.mp4', 1, 1, 19800, 1, 'The Social Network (2010) - Movie Details & Stream | CinemaVault', 'Stream The Social Network. Follow the birth of Facebook and the ruthless race to billionaire status.', 'social network, billionaire, tech startups, silicon valley', ''),

(3, 'Limitless', 'limitless', 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=600&q=80', 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1600&q=80', 'A struggling writer gains access to an experimental drug that unlocks 100% of his brain capacity, catapulting him into Wall Street.', 'Eddie Morra is an unemployed writer whose girlfriend Lindy has broken up with him. Eddie believes he has zero future, but when an old acquaintance introduces him to NZT, a revolutionary pharmaceutical pill that enables the human brain to operate at 100% efficiency, his life transforms overnight. He masters algorithms, foreign currencies, languages, and high finance.', 3, 'Sci-Fi, Thriller', 'English', 2011, '105 min', 7.4, 'mindset, peak performance, trading, brain power, finance', 'https://www.youtube.com/embed/4TLppsfzQH8', 'https://www.youtube.com/watch?v=4TLppsfzQH8', 'https://archive.org/download/sample-cinemavault/limitless.mp4', 1, 1, 23500, 1, 'Limitless (2011) - Mindset & Brain Performance Movie | CinemaVault', 'Watch Limitless. Discover the ultimate movie on mental dominance, trading mastery, and peak cognition.', 'limitless, nzt 48, mindset movie, stock trading', ''),

(4, 'The Big Short', 'the-big-short', 'https://images.unsplash.com/photo-1642543492481-44e81e3914a7?auto=format&fit=crop&w=600&q=80', 'https://images.unsplash.com/photo-1590283603385-17ffb3a7f29f?auto=format&fit=crop&w=1600&q=80', 'Three separate groups of financial analysts foresee the collapse of the US housing bubble and take on the global banks.', 'In 2006–2007, a group of eccentric investors realized the United States housing market was a powder keg built on fraudulent subprime mortgages. By taking counter-bets against the entire financial system, they profited billions while navigating the ethical turbulence of global collapse. Masterful direction by Adam McKay featuring Christian Bale and Ryan Gosling.', 4, 'Biography, Comedy, Drama', 'English', 2015, '130 min', 7.8, 'business, banking, wall street, economics, hedge fund', 'https://www.youtube.com/embed/vgqG3ITMv1Q', 'https://www.youtube.com/watch?v=vgqG3ITMv1Q', 'https://archive.org/download/sample-cinemavault/big-short.mp4', 1, 1, 17400, 1, 'The Big Short (2015) - Business & Market Mastery | CinemaVault', 'Watch The Big Short online. Learn how hedge fund visionaries predicted the biggest crisis in history.', 'the big short, financial crisis, hedge funds, business', ''),

(5, 'Margin Call', 'margin-call', 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?auto=format&fit=crop&w=600&q=80', 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=1600&q=80', 'Follows the key personnel at an investment bank over a tense 24-hour period during the initial stages of a financial panic.', 'Set in the high-pressure trading floors of Manhattan, Margin Call takes viewers inside a legendary investment firm over a grueling 36 hours. When a risk analyst uncovers a mathematical model revealing leverage liabilities that exceed the firm’s total market capitalization, leadership must choose between survival and global chaos. Essential watching for Forex traders and market operators.', 5, 'Drama, Thriller', 'English', 2011, '107 min', 7.2, 'forex, risk management, trading, investment bank, wall street', 'https://www.youtube.com/embed/IjZ-ke1kJ5U', 'https://www.youtube.com/watch?v=IjZ-ke1kJ5U', 'https://archive.org/download/sample-cinemavault/margin-call.mp4', 1, 1, 12800, 1, 'Margin Call (2011) - Forex & Risk Management Cinema | CinemaVault', 'Watch Margin Call. A thrilling look inside institutional trading desks and risk management.', 'margin call, forex movie, trading, finance thriller', ''),

(6, 'The Pursuit of Happyness', 'the-pursuit-of-happyness', 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=600&q=80', 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1600&q=80', 'A struggling salesman takes custody of his young son as he begins a life-changing, unpaid stockbroker internship.', 'Chris Gardner is a family man struggling to make ends meet. Despite his valiant attempts to keep the family afloat, the mother of his five-year-old son Christopher succumbs to the constant strain and departs. Chris lands an unpaid internship at a prestigious stock brokerage firm where only one in twenty will be hired. A true testament to unbreakable willpower and fatherhood.', 6, 'Biography, Drama', 'English', 2006, '117 min', 8.0, 'motivational, willpower, stockbroker, perseverance, will smith', 'https://www.youtube.com/embed/DMOBlEcRuw8', 'https://www.youtube.com/watch?v=DMOBlEcRuw8', 'https://archive.org/download/sample-cinemavault/pursuit-of-happyness.mp4', 1, 1, 31000, 1, 'The Pursuit of Happyness (2006) - Motivational Masterpiece | CinemaVault', 'Stream The Pursuit of Happyness. Experience the incredible journey of Chris Gardner from homelessness to wealth.', 'pursuit of happyness, motivational film, mindset, will smith', ''),

(7, 'John Wick: Chapter 4', 'john-wick-chapter-4', 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?auto=format&fit=crop&w=600&q=80', 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1600&q=80', 'John Wick uncovers a path to defeating The High Table, but must face a new enemy with powerful alliances across the globe.', 'With the price on his head ever increasing, legendary hitman John Wick takes his fight against the High Table global as he seeks out the most powerful players in the underworld, from New York to Paris, Japan to Berlin. Stunning cinematography, neon-drenched Parisian landscapes, and unparalleled martial choreography.', 7, 'Action, Crime, Thriller', 'English', 2023, '169 min', 7.7, 'action, keanu reeves, assassin, martial arts, blockbuster', 'https://www.youtube.com/embed/qEVUtrk8_B4', 'https://www.youtube.com/watch?v=qEVUtrk8_B4', 'https://archive.org/download/sample-cinemavault/john-wick-4.mp4', 1, 1, 28400, 1, 'John Wick: Chapter 4 (2023) - Ultra Action Blockbuster | CinemaVault', 'Stream John Wick 4 in 4K HDR. Neo-noir cinematic action at its absolute peak.', 'john wick 4, action blockbuster, keanu reeves', ''),

(8, 'Inception', 'inception', 'https://images.unsplash.com/photo-1478760329108-5c3ed9d495a0?auto=format&fit=crop&w=600&q=80', 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1600&q=80', 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea.', 'Dom Cobb is a skilled thief, the absolute best in the dangerous art of extraction: stealing valuable secrets from deep within the subconscious during the dream state. Cobb’s rare ability has made him a coveted player in this treacherous new world of corporate espionage, but it has also cost him everything he loves. Directed by Christopher Nolan.', 8, 'Action, Sci-Fi, Thriller', 'English', 2010, '148 min', 8.8, 'thriller, christopher nolan, mind bending, subconscious, sci-fi', 'https://www.youtube.com/embed/YoHD9XEInc0', 'https://www.youtube.com/watch?v=YoHD9XEInc0', 'https://archive.org/download/sample-cinemavault/inception.mp4', 1, 1, 45200, 1, 'Inception (2010) - Mind-Bending Thriller | CinemaVault', 'Stream Inception. Christopher Nolan’s legendary sci-fi masterpiece exploring the depths of the mind.', 'inception, christopher nolan, thriller, leonardo dicaprio', ''),

(9, 'K.G.F: Chapter 2', 'kgf-chapter-2', 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=600&q=80', 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1600&q=80', 'In the blood-soaked Kolar Gold Fields, Rocky’s name strikes fear into his foes while government forces prepare for an assault.', 'The blood-soaked land of Kolar Gold Fields has a new overlord now: Rocky, whose name strikes fear into the heart of his foes. His allies look up to him as their savior, the government sees him as a threat to law and order, and enemies are clamoring for revenge. One of the highest-grossing Indian cinematic epics of all time.', 9, 'Action, Crime, Drama', 'Hindi', 2022, '168 min', 8.3, 'bollywood, hindi, kgf, yash, south indian epic, gold mine', 'https://www.youtube.com/embed/JKa05nyUmuQ', 'https://www.youtube.com/watch?v=JKa05nyUmuQ', 'https://archive.org/download/sample-cinemavault/kgf-2.mp4', 1, 1, 56000, 1, 'K.G.F: Chapter 2 (Hindi) - Indian Cinema Epic | CinemaVault', 'Watch KGF Chapter 2 in Hindi. Rocky Bhai’s monumental empire and explosive showdown.', 'kgf 2, hindi movie, bollywood action, blockbuster', ''),

(10, 'Dangals of Ambition: Dangal', 'dangal', 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=600&q=80', 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1600&q=80', 'Former wrestler Mahavir Singh Phogat trains his daughters Geeta and Babita to become world-class champions against societal prejudice.', 'Biographical sports drama chronicling the unbelievable journey of former national wrestling champion Mahavir Singh Phogat, who defies orthodox traditions and trains his young daughters to become world-class gold medalists at the Commonwealth Games. A masterwork in coaching psychology, relentless discipline, and champion mindset.', 10, 'Action, Biography, Drama', 'Hindi', 2016, '161 min', 8.4, 'hindi, dangal, aamir khan, mindset, sports champion, inspirational', 'https://www.youtube.com/embed/x_7YlGv9u1g', 'https://www.youtube.com/watch?v=x_7YlGv9u1g', 'https://archive.org/download/sample-cinemavault/dangal.mp4', 0, 1, 38900, 1, 'Dangal (Hindi) - Champion Mindset Masterpiece | CinemaVault', 'Stream Dangal in Hindi. The ultimate sports drama on dedication, coaching, and victory.', 'dangal, hindi cinema, aamir khan, motivational sports', ''),

(11, 'The Big Lebowski', 'the-big-lebowski', 'https://images.unsplash.com/photo-1514306191717-452ec28c7814?auto=format&fit=crop&w=600&q=80', 'https://images.unsplash.com/photo-1514306191717-452ec28c7814?auto=format&fit=crop&w=1600&q=80', 'Jeff The Dude Lebowski, mistaken for a millionaire of the same name, seeks restitution for his ruined rug and gets roped into a ransom.', 'The Coen Brothers comedy masterpiece about Jeffrey "The Dude" Lebowski, an easygoing bowler in Los Angeles whose peaceful life is derailed when two thugs mistake him for a millionaire philanthropist with the same name. Featuring iconic dialogue and unforgettable characters.', 11, 'Comedy, Crime', 'English', 1998, '117 min', 8.1, 'comedy, cult classic, coen brothers, the dude', 'https://www.youtube.com/embed/cd-go0oBF4Y', 'https://www.youtube.com/watch?v=cd-go0oBF4Y', 'https://archive.org/download/sample-cinemavault/big-lebowski.mp4', 0, 1, 14300, 1, 'The Big Lebowski - Comedy Cult Classic | CinemaVault', 'Stream The Big Lebowski. The hilarious cult classic comedy directed by the Coen Brothers.', 'the big lebowski, comedy, cult movie', ''),

(12, 'La La Land', 'la-la-land', 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=600&q=80', 'https://images.unsplash.com/photo-1514306191717-452ec28c7814?auto=format&fit=crop&w=1600&q=80', 'While navigating their careers in Los Angeles, a pianist and an actress fall in love while attempting to reconcile their dreams.', 'Sebastian and Mia are drawn together by their common desire to do what they love. But as success mounts, they are faced with decisions that begin to fray the fragile fabric of their love affair, and the dreams they worked so hard to maintain in each other threaten to rip them apart. Academy Award winning romantic visual feast.', 12, 'Comedy, Drama, Music', 'English', 2016, '128 min', 8.0, 'romantic, music, hollywood, ryan gosling, emma stone', 'https://www.youtube.com/embed/0pdqf4P9MB8', 'https://www.youtube.com/watch?v=0pdqf4P9MB8', 'https://archive.org/download/sample-cinemavault/la-la-land.mp4', 0, 1, 21900, 1, 'La La Land (2016) - Romantic Cinematic Feast | CinemaVault', 'Watch La La Land. A breathtaking romantic musical about ambition, dreams and love in Los Angeles.', 'la la land, romance, musical, ryan gosling', '');

-- --------------------------------------------------------
-- Seed Data: Featured Hero Banners (Admin Configurable)
-- --------------------------------------------------------
INSERT INTO `featured_banners` (`id`, `title`, `subtitle`, `badge`, `banner_image`, `movie_id`, `target_url`, `button_text`, `sort_order`, `status`) VALUES
(1, 'THE WOLF OF WALL STREET', 'Greed. Power. Colossal Wealth. The legendary rise and high-stakes chaos of Jordan Belfort.', 'EXCLUSIVE PREMIERE', 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?auto=format&fit=crop&w=1600&q=80', 1, 'movie.php?slug=the-wolf-of-wall-street', 'Watch Movie Now', 1, 1),
(2, 'THE SOCIAL NETWORK', 'You don’t get to 500 million friends without making a few enemies. The billionaire tech saga.', 'BILLIONAIRE SPOTLIGHT', 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1600&q=80', 2, 'movie.php?slug=the-social-network', 'Stream Film', 2, 1),
(3, 'LIMITLESS', 'What if a single pill could unlock 100% of your cognitive power? Dominate the global markets.', 'MINDSET MASTERCLASS', 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1600&q=80', 3, 'movie.php?slug=limitless', 'Explore Movie', 3, 1),
(4, 'JOHN WICK: CHAPTER 4', 'The High Table will fall. Experience the world’s most visually breathtaking neo-noir action spectacle.', 'ACTION BLOCKBUSTER', 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1600&q=80', 7, 'movie.php?slug=john-wick-chapter-4', 'Watch In 4K HDR', 4, 1);

SET FOREIGN_KEY_CHECKS = 1;
