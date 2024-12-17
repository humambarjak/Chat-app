    <?php
    session_start();
    require 'db.php';

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    // Fetch user details
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $username = $user['username'];
        $profile_picture = $user['profile_picture'] ? $user['profile_picture'] : "uploads/default.png";
        $email = $username . "@gmail.com"; // Email is dynamically created
    } else {
        echo "Error: User not found.";
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Chat App</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
    <div id="unread-notification" style="display: none; position: fixed; top: 10px; right: 10px; background: #f44336; color: white; padding: 10px; border-radius: 5px; z-index: 1000;"></div>
    <div id="reminder-area" style="position: fixed; bottom: 10px; right: 10px; z-index: 1000;"></div>
            <!-- Sidebar for user list -->
            <div id="sidebar">
            <!-- Contacts and Theme Switch -->
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 id="contact">Contacts</h3>
                <div id="theme-switcher">
                    <label class="switch">
                        <input type="checkbox" id="theme-toggle">
                        <span class="slider"></span>
                    </label>
                    <span id="theme-label">Dark Mode</span>
                </div>
            </div>
            
            <!-- User list -->
            <ul id="contacts">
                <li><img src="https://via.placeholder.com/40" alt="User"> User 1</li>
                <li><img src="https://via.placeholder.com/40" alt="User"> User 2</li>
            </ul>
        </div>

        <!-- Chat area -->
        <div id="chat-area">
            <!-- Chat header -->
            <div id="chat-header">
                <img src="https://via.placeholder.com/40" alt="User Avatar" id="chat-header-img">
                <div id="chat-header-info">
                    <h3 id="chat-header-name">Select a user</h3>
                    <p id="chat-header-status">Last seen: N/A</p>
                </div>
            </div>
         <!-- Messages section -->
            <div id="messages"></div>
            <!-- Input area -->
            <div id="message-input-area">
                <!-- Plus Button -->
                <div class="blus-container">
                <!-- Plus Button -->
                <button id="plus-button" class="blusButton">
                    <svg class="plusIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30">
                        <g>
                            <path d="M13.75 23.75V16.25H6.25V13.75H13.75V6.25H16.25V13.75H23.75V16.25H16.25V23.75H13.75Z"></path>
                        </g>
                    </svg>
                </button>
    
             <!-- Dropdown Menu -->
            <div id="blus-menu" class="blus-menu">
                <button id="photo-button" class="menu-option">Send Photo</button>
                <input type="file" id="photo-input" accept="image/*" style="display: none;" />
                <button id="video-button" class="menu-option">Send Video</button>
                <input type="file" id="video-input" accept="video/*" style="display: none;" />
            </div>
        </div>

    <!-- Input text for messages -->
            <input type="text" id="message-input" placeholder="Text Message" />
            <button id="send-button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                    <path d="M2.01 21L23 12 2.01 3v7l15 2-15 2z" />
                </svg>
            </button>
        </div>
        </div>


    <!-- User profile section -->
    <div id="profile">
    <div id="profile-header">
        <img src="<?php echo $profile_picture; ?>" alt="User Avatar" id="profile-picture">
        <h3 id="profile-name"><?php echo htmlspecialchars($username); ?></h3>
    </div>
    <p id="profile-info">Email: <?php echo htmlspecialchars($email); ?></p>
    <button id="block-user" class="button-common">Block User</button>
    <button id="logout-button" class="button-common">Logout</button>
    <a href="profile.php" class="auth-button button-common">Edit Profile</a>

    <!-- New button for changing the chat background -->
    <button id="change-bg-button" class="button-common">Change Chat Background</button>
</div>
            <!-- boot box -->
<div id="custom-prompt-modal" class="custom-modal">
    <div class="custom-modal-content">
        <h3>Enter Background URL or Color</h3>
        <input type="text" id="custom-prompt-input" placeholder="e.g., #f0f0f0, rgba(0,0,0,0.5), or URL" />
        <div class="custom-modal-actions">
            <button id="custom-prompt-ok" class="auth-button">OK</button>
            <button id="custom-prompt-cancel" class="auth-button cancel">Cancel</button>
        </div>
    </div>
</div>
    <div id="unread-notification" style="display: none; position: fixed; top: 10px; right: 10px; background: #f44336; color: white; padding: 10px; border-radius: 5px; z-index: 1000;"></div>
    <div id="reminder-area" style="position: fixed; bottom: 10px; right: 10px; z-index: 1000;"></div>

    <div id="emoji-menu" style="display: none; position: absolute; background: #333; padding: 10px; border-radius: 5px; z-index: 100;">
    <span onclick="addReaction('😊')">😊</span>
    <span onclick="addReaction('👍')">👍</span>
    <span onclick="addReaction('❤️')">❤️</span>
    <span onclick="addReaction('😂')">😂</span>
    <span onclick="addReaction('😮')">😮</span>
    <!-- Add more emojis as needed -->
</div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let chatPartnerId = null;

        // Fetch user list
        function fetchUsers() {
            $.get('fetch_users.php', function (users) {
                let html = '';
                users.forEach(function (user, index) {
                    html += `
                        <li onclick="selectUser(${user.id}, '${user.username}')">
                            <img src="${user.profile_picture}" alt="${user.username}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                            ${user.username}
                        </li>`;
                    // Automatically select the first user as the chat partner
                    if (index === 0 && !chatPartnerId) {
                        selectUser(user.id, user.username);
                    }
                });
                $('#contacts').html(html);
            }, 'json');
        }

        // Dark/Light Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const themeLabel = document.getElementById('theme-label');

        // Check localStorage for saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.body.classList.add(savedTheme);
            themeToggle.checked = savedTheme === 'dark-theme';
            themeLabel.textContent = savedTheme === 'dark-theme' ? 'Light Mode' : 'Dark Mode';
        }

        // Toggle Theme
        themeToggle.addEventListener('change', () => {
            if (themeToggle.checked) {
                document.body.classList.add('dark-theme');
                document.body.classList.remove('light-theme');
                localStorage.setItem('theme', 'dark-theme');
                themeLabel.textContent = 'Light Mode';
            } else {
                document.body.classList.add('light-theme');
                document.body.classList.remove('dark-theme');
                localStorage.setItem('theme', 'light-theme');
                themeLabel.textContent = 'Dark Mode';
            }
        });

        // Select a user to chat with
        function selectUser(userId, username) {
            chatPartnerId = userId;

            // Fetch user details
            $.get('fetch_user_details.php', { user_id: userId }, function (user) {
                // Update the chat header
                $('#chat-header-img').attr('src', user.profile_picture);
                $('#chat-header-name').text(user.username);
                $('#chat-header-status').text(`Last seen: ${user.last_seen || 'N/A'}`);

                // Clear and fetch messages
                $('#messages').html('');
                fetchMessages();
            }, 'json');
        }

        // Scroll to the bottom of messages
        function scrollToBottom() {
            const messagesContainer = document.getElementById('messages');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        function fetchMessages() {
    if (!chatPartnerId) return;

    $.get('fetch_messages.php', { chat_partner_id: chatPartnerId }, function (response) {
        const { messages, unread_count } = response;

        // Render messages
        renderMessages(messages);

        // Dynamically display or clear notifications
        if (unread_count > 0) {
            displayUnreadNotification(unread_count);
        } else {
            clearUnreadNotification();
        }
    }, 'json').fail(function () {
        console.error('Failed to fetch messages');
    });
}


function renderMessages(messages) {
    let html = '';
    messages.forEach(msg => {
        const messageClass = msg.sender_id === <?php echo $_SESSION['user_id']; ?> ? 'sent' : 'received';
        let content = '';

        if (msg.type === 'photo') {
            content = `<img src="uploads/photos/${msg.content}" alt="Sent Photo" style="max-width: 200px; border-radius: 8px;">`;
        } else if (msg.type === 'video') {
            content = `<video controls style="max-width: 200px; border-radius: 8px;">
                           <source src="uploads/videos/${msg.content}" type="video/mp4">
                           Your browser does not support the video tag.
                       </video>`;
        } else {
            content = msg.message;
        }

        html += `
            <div class="message ${messageClass}" data-message-id="${msg.id}">
                ${content}
                <div class="reactions">
                    <span class="reaction-list">${msg.reactions || ''}</span>
                    <button class="reaction-button" onclick="showEmojiMenu(${msg.id})">+</button>
                </div>
            </div>`;
    });
    $('#messages').html(html);
    scrollToBottom();
}
function displayUnreadNotification(count) {
    const notificationArea = $('#unread-notification');
    notificationArea.text(`You have ${count} unread message(s)`).show();
}


function clearUnreadNotification() {
    const notificationArea = $('#unread-notification');
    notificationArea.text('').hide(); // Clear text and hide notification
}

// try new one
document.addEventListener('DOMContentLoaded', function () {
    const plusButton = document.getElementById('plus-button');
    const plusMenu = document.getElementById('blus-menu');
    const photoInput = document.getElementById('photo-input');
    const videoInput = document.getElementById('video-input');

    // Toggle the visibility of the menu
    plusButton.addEventListener('click', () => {
        const isMenuVisible = plusMenu.style.display === 'block';
        plusMenu.style.display = isMenuVisible ? 'none' : 'block';
    });

    // Event for sending a photo
    document.getElementById('photo-button').addEventListener('click', () => {
        photoInput.click();
    });

    // Handle photo input change
    photoInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            uploadFile(file, 'photo');
        }
    });

    // Event for sending a video
    document.getElementById('video-button').addEventListener('click', () => {
        videoInput.click();
    });

    // Handle video input change
    videoInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            uploadFile(file, 'video');
        }
    });

    // Function to upload a file
    function uploadFile(file, fileType) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', fileType);

        fetch('upload_file.php', {
            method: 'POST',
            body: formData
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert(`${fileType} uploaded successfully: ${data.filename}`);
                    // Optionally, you can append the file to the chat messages
                    // Example:
                    // displayMessage(`${fileType} sent: ${data.filename}`);
                } else {
                    alert(`Failed to upload ${fileType}: ${data.error}`);
                }
            })
            .catch((error) => {
                console.error('Error uploading file:', error);
                alert(`An error occurred while uploading the ${fileType}.`);
            });
    }

    // Close the menu if clicking outside
    document.addEventListener('click', (event) => {
        if (!plusButton.contains(event.target) && !plusMenu.contains(event.target)) {
            plusMenu.style.display = 'none';
        }
    });
});

// Mark messages as read when selecting a user
function markMessagesAsRead() {
    if (!chatPartnerId) return;

    $.post('mark_messages_read.php', { chat_partner_id: chatPartnerId }, function (response) {
        console.log('Messages marked as read');
        clearUnreadNotification(); // Clear the notification
    }, 'json').fail(function () {
        console.error('Failed to mark messages as read');
    });
}

// Call markMessagesAsRead when selecting a user
function selectUser(userId, username) {
    chatPartnerId = userId;

    // Fetch user details
    $.get('fetch_user_details.php', { user_id: userId }, function (user) {
        $('#chat-header-img').attr('src', user.profile_picture);
        $('#chat-header-name').text(user.username);
        $('#chat-header-status').text(`Last seen: ${user.last_seen || 'N/A'}`);
        $('#messages').html('');

        // Fetch messages and mark them as read
        fetchMessages();
        markMessagesAsRead(); // Mark messages as read after selecting the user
    }, 'json').fail(function () {
        console.error('Failed to fetch user details');
    });
}


// Show emoji menu
function showEmojiMenu(messageId) {
    const menu = $('#emoji-menu');
    const messageElement = $(`[data-message-id="${messageId}"]`);
    const offset = messageElement.offset();

    // Position the emoji menu near the message
    menu.css({ 
        top: offset.top - 50 + 'px',
        left: offset.left + 'px',
        display: 'block'
    }).data('message-id', messageId);
}

// Add reaction to a message
function addReaction(emoji) {
    const messageId = $('#emoji-menu').data('message-id');
    if (!messageId) return;

    $.post('add_reaction.php', { message_id: messageId, reaction: emoji }, function (response) {
        fetchMessages(); // Refresh messages to display the reaction
        $('#emoji-menu').hide(); // Hide the menu after selection
    }).fail(function () {
        console.error('Failed to add reaction');
    });
}

// Hide emoji menu when clicking outside
$(document).click(function (e) {
    if (!$(e.target).closest('#emoji-menu').length && !$(e.target).hasClass('reaction-button')) {
        $('#emoji-menu').hide();
    }
});

// Function to apply the saved background for the selected user
function applyChatBackground(userId) {
    const storedBackgrounds = JSON.parse(localStorage.getItem('chatBackgrounds')) || {};
    const userBg = storedBackgrounds[userId];

    if (userBg) {
        const messagesArea = document.getElementById('messages');
        // Check if it's a URL or a color
        if (userBg.startsWith('http')) {
            messagesArea.style.backgroundImage = `url('${userBg}')`;
            messagesArea.style.backgroundSize = 'cover';
        } else {
            messagesArea.style.backgroundImage = 'none';
            messagesArea.style.backgroundColor = userBg;
        }
    } else {
        // Reset to default background if none is saved
        document.getElementById('messages').style.background = '';
    }
}

// Handle the custom modal for background changes
document.getElementById('change-bg-button').addEventListener('click', function () {
    if (!chatPartnerId) {
        alert("Please select a user to change the background.");
        return;
    }

    const modal = document.getElementById('custom-prompt-modal');
    const input = document.getElementById('custom-prompt-input');
    const okButton = document.getElementById('custom-prompt-ok');
    const cancelButton = document.getElementById('custom-prompt-cancel');

    // Clear previous input
    input.value = '';
    
    // Show the modal
    modal.style.display = 'flex';

    // Handle OK button click
    okButton.onclick = function () {
        const backgroundValue = input.value.trim();
        if (backgroundValue) {
            // Save the background for this user in localStorage
            const storedBackgrounds = JSON.parse(localStorage.getItem('chatBackgrounds')) || {};
            storedBackgrounds[chatPartnerId] = backgroundValue;
            localStorage.setItem('chatBackgrounds', JSON.stringify(storedBackgrounds));

            // Apply the new background
            applyChatBackground(chatPartnerId);
        }
        modal.style.display = 'none'; // Close modal
    };

    // Handle Cancel button click
    cancelButton.onclick = function () {
        modal.style.display = 'none'; // Close modal
    };
});

// Close modal if clicking outside of it
document.addEventListener('click', function (event) {
    const modal = document.getElementById('custom-prompt-modal');
    if (event.target === modal) {
        modal.style.display = 'none'; // Close modal
    }
});

// Apply the background when a user is selected
function selectUser(userId, username) {
    chatPartnerId = userId;

    // Fetch user details
    $.get('fetch_user_details.php', { user_id: userId }, function (user) {
        $('#chat-header-img').attr('src', user.profile_picture);
        $('#chat-header-name').text(user.username);
        $('#chat-header-status').text(`Last seen: ${user.last_seen || 'N/A'}`);
        $('#messages').html('');

        // Fetch messages and apply the background for the user
        fetchMessages();
        applyChatBackground(userId); // Apply background
        markMessagesAsRead();
    }, 'json').fail(function () {
        console.error('Failed to fetch user details');
    });
}


function checkForMessageReminders() {
    $.get('check_unread_messages.php', function (reminders) {
        reminders.forEach(reminder => {
            // Display reminder notification
            displayReminderNotification(reminder);
        });
    }, 'json').fail(function () {
        console.error('Failed to fetch message reminders');
    });
}

function displayReminderNotification(reminder) {
    const notificationHTML = `
        <div class="reminder-notification">
            <p><strong>User ${reminder.sender_id}</strong> has an unread message for <strong>${Math.round(reminder.time_since_sent / 60)} minutes</strong>.</p>
            <p>Message: "${reminder.message}"</p>
        </div>
    `;
    $('#reminder-area').append(notificationHTML);

    // Auto-hide after 5 seconds
    setTimeout(() => {
        $('#reminder-area .reminder-notification').first().remove();
    }, 5000);
}

// Poll for message reminders every minute
setInterval(checkForMessageReminders, 60000); // 60 seconds



        // Send message using the button or "Enter" key
        function sendMessage() {
            let message = $('#message-input').val();
            if (!chatPartnerId) {
                $('#messages').html('<div class="info">Please select a user to chat with.</div>');
                return;
            }
            if (message.trim() !== '') {
                $.post('send_message.php', { message: message, receiver_id: chatPartnerId }, function (response) {
                    console.log('Response from server:', response);
                    $('#message-input').val(''); // Clear input field
                    fetchMessages(); // Refresh chat messages
                    scrollToBottom(); // Scroll to the newest message
                }, 'json').fail(function (xhr, status, error) {
                    console.error('Send Message Error:', error);
                });
            } else {
                $('#messages').append('<div class="info">Message cannot be empty.</div>');
            }
        }

        // Trigger sendMessage on "Enter" key press
        $('#message-input').keypress(function (e) {
            if (e.which === 13) { // 13 is the keycode for "Enter"
                e.preventDefault(); // Prevent default form submission
                sendMessage();
            }
        });

        // Send message when clicking the send button
        $('#send-button').click(function () {
            sendMessage();
        });

        // Logout functionality
        $('#logout-button').click(function () {
            window.location.href = 'logout.php'; // Redirect to logout.php
        });

        // Poll for messages every second
        setInterval(fetchMessages, 1000);

        // Load users on page load
        fetchUsers();
    </script>

    </body>
    </html>
