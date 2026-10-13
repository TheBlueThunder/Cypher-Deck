<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Cypher Deck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.php">
    <style>
        .contact-container {
            max-width: 700px;
            margin: 3rem auto;
        }

        .contact-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: rgba(0, 255, 102, 0.1);
            border: 2px solid #00ff66;
            border-radius: 8px;
            box-shadow: 0 0 30px rgba(0, 255, 102, 0.3);
        }

        .contact-header h1 {
            color: #00ff66;
            text-shadow: 0 0 20px rgba(0, 255, 102, 0.5);
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .contact-subtext {
            color: #ddd;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            color: #00ff66;
            font-weight: bold;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #444;
            border-radius: 4px;
            color: #ddd;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #00ff66;
            box-shadow: 0 0 15px rgba(0, 255, 102, 0.3);
            background: rgba(0, 0, 0, 0.7);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #00ff66, #00bfff);
            color: #000;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(0, 255, 102, 0.6);
        }

        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            display: none;
        }

        .alert.success {
            background: rgba(40, 167, 69, 0.2);
            border: 2px solid #28a745;
            color: #28a745;
        }

        .alert.error {
            background: rgba(220, 53, 69, 0.2);
            border: 2px solid #dc3545;
            color: #dc3545;
        }

        .alert.show {
            display: block;
        }

        .char-count {
            color: #999;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
    </style>
    <base href="/cypherdeck-main1/">
</head>
<body>
    <?php require_once("../templates/navbar.php"); ?>

    <main class="container">
        <div class="contact-container main-box">
            <!-- Contact header -->
            <div class="contact-header">
                <h1>📧 CONTACT US</h1>
                <p class="contact-subtext">Have feedback, bugs to report, or just want to say hello? Send us a message!</p>
            </div>

            <!-- Alert messages -->
            <div class="alert success" id="successAlert">
                ✓ Message sent successfully! Thank you for reaching out.
            </div>
            <div class="alert error" id="errorAlert">
                Error: <span id="errorMessage"></span>
            </div>

            <!-- Contact form -->
            <form id="contactForm">
                <?php require_once("../php/csrf_helper.php"); ?>
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                
                <!-- Name field -->
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                </div>

                <!-- Email field -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" required>
                </div>

                <!-- Subject field -->
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" placeholder="What is this about?" maxlength="100" required>
                </div>

                <!-- Message field -->
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" placeholder="Tell us what you think..." maxlength="2000" required></textarea>
                    <div class="char-count">
                        <span id="charCount">0</span> / 2000 characters
                    </div>
                </div>

                <!-- Submit button -->
                <button type="submit" class="submit-btn" id="submitBtn">Send Message</button>
            </form>

            <!-- Info box -->
            <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(0, 0, 0, 0.5); border: 2px solid #444; border-radius: 8px;">
                <h4 style="color: #00ff66; margin-bottom: 1rem;">📬 Other Ways to Reach Us</h4>
                <p style="color: #ddd; margin-bottom: 0.5rem;">
                    <strong>Report Bugs:</strong> Use this form to report any bugs or issues you encounter.
                </p>
                <p style="color: #ddd; margin-bottom: 0.5rem;">
                    <strong>Feature Requests:</strong> Have an idea for a new feature? Let us know!
                </p>
                <p style="color: #ddd;">
                    <strong>General Feedback:</strong> Your feedback helps us make Cypher Deck better.
                </p>
            </div>
        </div>
    </main>

    <footer class="text-center mt-5 mb-3 text-secondary">
        <small>&copy; <?php echo date('Y'); ?> Cypher Deck</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update character counter
        const messageField = document.getElementById('message');
        const charCount = document.getElementById('charCount');
        
        messageField.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });

        // Handle form submission
        document.getElementById('contactForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');
            
            // Hide previous alerts
            successAlert.classList.remove('show');
            errorAlert.classList.remove('show');
            
            // Disable submit button while sending
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            
            // Create form data
            const formData = new FormData(this);
            
            try {
                // Send to backend
                const response = await fetch('/cypherdeck-main1/pages/php/process_contact.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Show success message
                    successAlert.classList.add('show');
                    
                    // Reset form
                    document.getElementById('contactForm').reset();
                    charCount.textContent = '0';
                    
                    // Reset button
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Send Message';
                    }, 2000);
                } else {
                    // Show error message
                    errorMessage.textContent = data.error || 'Failed to send message';
                    errorAlert.classList.add('show');
                    
                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Send Message';
                }
            } catch (error) {
                // Network error
                errorMessage.textContent = 'Network error: ' + error.message;
                errorAlert.classList.add('show');
                
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Message';
            }
        });
    </script>
</body>
</html>
