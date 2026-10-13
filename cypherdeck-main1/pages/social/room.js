let draggedElement = null;
let offsetX = 0;
let offsetY = 0;
let resizingWindow = null;
let resizeStartX = 0;
let resizeStartY = 0;
let resizeStartWidth = 0;
let resizeStartHeight = 0;


// Let me resize windows by dragging from the bottom right corner
function startResize(event, windowId) {
    const windowEl = document.getElementById(windowId);
    const startX = event.clientX;
    const startY = event.clientY;
    const startWidth = windowEl.offsetWidth;
    const startHeight = windowEl.offsetHeight;
    
    function handleResize(e) {
        // Calculate how far the mouse moved
        const deltaX = e.clientX - startX;
        const deltaY = e.clientY - startY;
        
        // Resize the window while keeping minimum size
        windowEl.style.width = Math.max(200, startWidth + deltaX) + 'px';
        windowEl.style.height = Math.max(150, startHeight + deltaY) + 'px';
    }
    
    // Stop resizing when mouse is released
    function stopResize() {
        document.removeEventListener('mousemove', handleResize);
        document.removeEventListener('mouseup', stopResize);
    }
    
    document.addEventListener('mousemove', handleResize);
    document.addEventListener('mouseup', stopResize);
    event.preventDefault();
}

// Make windows draggable when I grab the titlebar
function makeDraggable(element) {
  let titlebar = element.querySelector('.window-titlebar');
  
  titlebar.addEventListener('mousedown', (e) => {
    draggedElement = element;
    offsetX = e.clientX - element.offsetLeft;
    offsetY = e.clientY - element.offsetTop;
    element.style.zIndex = 1000;
  });
}

// Move the window as I drag it
document.addEventListener('mousemove', (e) => {
  if (draggedElement) {
    draggedElement.style.left = (e.clientX - offsetX) + 'px';
    draggedElement.style.top = (e.clientY - offsetY) + 'px';
  }
});

// Stop dragging when I release the mouse
document.addEventListener('mouseup', () => {
  draggedElement = null;
});

// Make sticky notes draggable and save their position
function makeStickyDraggable(element) {
  let offsetX = 0, offsetY = 0;

  element.addEventListener('mousedown', (e) => {
    offsetX = e.clientX - element.offsetLeft;
    offsetY = e.clientY - element.offsetTop;
    element.style.zIndex = 150;

    function moveAt(e) {
      element.style.left = (e.clientX - offsetX) + 'px';
      element.style.top = (e.clientY - offsetY) + 'px';
    }

    function stopDragging() {
      document.removeEventListener('mousemove', moveAt);
      document.removeEventListener('mouseup', stopDragging);
      saveStickyPosition(element);
    }

    document.addEventListener('mousemove', moveAt);
    document.addEventListener('mouseup', stopDragging);
  });
}

// Toggle minimize to hide or show window content
function minimizeWindow(windowId) {
  const window = document.getElementById(windowId);
  const content = window.querySelector('.window-content');
  if (content.style.display === 'none') {
    content.style.display = 'block';
  } else {
    content.style.display = 'none';
  }
}

// Maximize window to full screen or restore to normal size
function maximizeWindow(windowId) {
  const window = document.getElementById(windowId);
  if (window.style.width === '100%') {
    window.style.width = windowId === 'chat-window' ? '50%' : '35%';
    window.style.height = '65%';
  } else {
    window.style.width = '100%';
    window.style.height = '100%';
  }
}

// Close window by hiding it
function closeWindow(windowId) {
  document.getElementById(windowId).style.display = 'none';
}

// Send chat message when pressing Enter
function handleChatInput(event, roomId) {
  if (event.key === 'Enter' && event.target.value.trim()) {
    const message = event.target.value;
    event.target.value = '';

    fetch('/cypherdeck-main1/pages/social/send_message.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `room_id=${roomId}&message=${encodeURIComponent(message)}`
    })
    .then(() => {
      refreshMessages(roomId);
    });
  }
}

// Poll for new messages every 3 seconds
let messageIntervalStarted = false;

function refreshMessages(roomId) {
  if (messageIntervalStarted) return;
  messageIntervalStarted = true;

  setInterval(() => {
    fetch(`/cypherdeck-main1/pages/social/get_messages.php?room_id=${roomId}`)
      .then(r => r.text())
      .then(html => {
        document.getElementById('messages').innerHTML = html;
        document.getElementById('chat-content').scrollTop =
            document.getElementById('chat-content').scrollHeight;
      });
  }, 3000);
}


// Create a new sticky note with title and content
function createNewSticky() { 
  const title = prompt('Note title (optional):');
  if (title === null) return; // User cancelled
  
  const content = prompt('Note content:');
  if (content === null) return; // User cancelled

  const roomId = document.querySelector('[data-room-id]')?.getAttribute('data-room-id') || '1';
  
  fetch('/cypherdeck-main1/pages/social/create_sticky.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `room_id=${roomId}&title=${encodeURIComponent(title || 'Untitled')}&content=${encodeURIComponent(content)}`
  })
  .then(response => response.json())
  .then(() => location.reload())
  .catch(err => console.error('Error creating sticky:', err));
}

// Edit a sticky note
function editSticky(noteId) {
  // Get current note data
  const note = document.querySelector(`[data-note-id="${noteId}"]`);
  const currentTitle = note.querySelector('.sticky-note-title').textContent;
  const currentContent = note.querySelector('.sticky-note-content').textContent;

  // Prompt for new values
  const newTitle = prompt('Edit note title:', currentTitle);
  if (newTitle === null) return; // User cancelled

  const newContent = prompt('Edit note content:', currentContent);
  if (newContent === null) return; // User cancelled

  fetch('/cypherdeck-main1/pages/social/edit_sticky.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `note_id=${noteId}&title=${encodeURIComponent(newTitle || 'Untitled')}&content=${encodeURIComponent(newContent)}`
  })
  .then(response => response.json())
  .then(() => location.reload())
  .catch(err => console.error('Error editing sticky:', err));
}

// Delete a sticky note after confirming
function deleteSticky(noteId) {
  if (confirm('Delete this sticky note?')) {
    fetch('/cypherdeck-main1/pages/social/delete_sticky.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `note_id=${noteId}`
    })
    .then(response => response.json())
    .then(() => location.reload())
    .catch(err => console.error('Error deleting sticky:', err));
  }
}

// Save the sticky note position to database
function saveStickyPosition(element) {
  const noteId = element.dataset.noteId;
  const x = element.offsetLeft;
  const y = element.offsetTop;

  fetch('/cypherdeck-main1/pages/social/save_sticky_position.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `note_id=${noteId}&x=${x}&y=${y}`
  })
  .catch(err => console.error('Error saving position:', err));
}

// Initialize everything when the page loads
document.addEventListener('DOMContentLoaded', () => {
  const chatWindow = document.getElementById('chat-window');
  const puzzlesWindow = document.getElementById('puzzles-window');
  
  if (chatWindow) makeDraggable(chatWindow);
  if (puzzlesWindow) makeDraggable(puzzlesWindow);

  document.querySelectorAll('.sticky-note').forEach(note => {
    makeStickyDraggable(note);
  });

  const roomId = document.querySelector('.desktop')?.getAttribute('data-room-id') || '1';
  refreshMessages(roomId);
});
