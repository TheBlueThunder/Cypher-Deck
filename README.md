#Cypher Deck
A-Level Computer Science coursework (awarded A*). A social puzzle platform for cybersecurity, where puzzles are presented as detective case files and solved in themed community rooms.

Built from scratch in PHP, MySQL, JavaScript and CSS. Users register and log in, solve puzzles with a hint system, track solved puzzles and compete on a leaderboard. Creators can submit their own puzzles, and an admin dashboard manages users. Includes two game modes: OVER//CLOCK (timed, with the whole interface re-themed by difficulty) and BurstRush (streak-based scoring).

Security was designed in and tested: bcrypt password hashing, parameterised queries, CSRF tokens and session expiry, checked against a test plan covering SQL injection, session hijacking, CSRF and malicious file upload.
