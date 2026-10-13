# CypherDeck

**A social cybersecurity puzzle platform.** Puzzles are presented as detective case files and solved together in themed community rooms.

Built as my A-Level Computer Science NEA (OCR H446), awarded A* for the coursework component.

## The idea

Cybersecurity platforms like TryHackMe, Hack The Box and picoCTF are great for learning, but they treat it as a solo activity and lean heavily on a constant connection. CypherDeck asks a different question: what if solving puzzles felt like hanging out somewhere with other people who enjoy the same things?

Users log in, browse puzzles, attempt them, unlock hints when stuck, and see their progress. Creators submit their own puzzles. The community rooms give people somewhere to talk through challenges together.

## Features

- **Accounts:** registration, login, logout, and an admin dashboard for managing users and viewing their points and solved puzzles
- **Puzzles:** creators set a title, description, questions, answers, tags, difficulty (Easy, Medium, Hard) and hints
- **Answer checking:** inputs are validated, normalised and compared against stored answers, with instant feedback and points awarded
- **Hints:** become available after a wrong attempt, so puzzles stay challenging without being frustrating
- **Progress tracking:** solved puzzles are recorded on the user's profile, and puzzles can be replayed to improve times
- **Leaderboard:** scores and achievements
- **OVER//CLOCK mode:** timed puzzles across four difficulty tiers. The whole interface re-themes (green, yellow, red, cyan) when you pick a difficulty, driven by a single CSS custom property
- **BurstRush mode:** quick-fire, per-question scoring with streak multipliers and a cap so long streaks cannot break leaderboard balance
- **Social rooms:** themed rooms (for example a Cryptography Den and a Steganography Lab) with sticky notes and a puzzle list, laid out like a desktop
- **Responsive design:** works across screen sizes

## Security

Security was designed in from the start rather than added at the end:

- Passwords stored as **bcrypt** hashes
- **Parameterised queries** (PDO) against SQL injection
- **CSRF tokens** on forms
- Session management with a 30-minute expiry and protected pages that redirect to login
- Role checks so only admins reach admin functions
- Input sanitisation before anything is stored

The design document includes a test plan of around 120 cases covering SQL injection, session hijacking, CSRF, password storage and malicious file upload.

## Built with

- PHP
- MySQL (managed through phpMyAdmin)
- JavaScript
- HTML and CSS

## Computational thinking

The project is organised around the standard methods:

- **Abstraction:** theming handled through CSS custom properties, so one change updates the whole interface
- **Decomposition:** separate modules for authentication, puzzle management, social features and game modes, each with its own files and tables
- **Pattern recognition:** consistent validation, query and feedback patterns, and a reusable difficulty colour system
- **Algorithmic thinking:** step-by-step answer validation (retrieve, normalise, compare, award points or log the attempt)
- **Evaluation:** a test plan and debug logging to find where users struggle

## Evaluation: what worked and what did not

| Area | Outcome |
| --- | --- |
| Secure registration and login | Fully met |
| Users can create and submit puzzles | Fully met |
| Instant feedback, hints, solved-puzzle tracking | Fully met |
| Responsive interface | Fully met |
| Detective case-file presentation | Fully met |
| Multiple puzzle types (text, image, code) | Partially met: text-based puzzles and categories only, no image or file puzzles |
| Real-time community rooms | Partially met: rooms, sticky notes and puzzle list built, cross-team social events not |
| Works fully offline | Not met: the app depends on the server, and puzzle downloads ran out of time |

## What I would do next

- Add image and file-based puzzles (steganography, forensics)
- Add downloadable puzzles and lessons so it works offline
- Build the cross-team social events
- Rebuild the project from the ground up with what I have learned since

## AI use

I used Claude for permitted purposes only: explaining concepts (prepared statements, CSRF tokens, `password_hash()`), generating placeholder data for testing, and naming and comment suggestions. No code was written by AI. All implementation, debugging and security work is my own.

## Author

Abdulla Riza Wazeerdeen
A-Level Computer Science NEA, Whitmore Sixth Form
[GitHub](https://github.com/TheBlueThunder) · [YouTube](https://youtube.com/@AceTechnologies2)
