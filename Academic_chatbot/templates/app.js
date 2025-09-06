// Mock data and local state
const mockUsers = [
  {
    id: 'u1',
    name: 'Alif Iqbal',
    teaches: ['JavaScript'],
    learns: ['Python'],
  },
  {
    id: 'u2',
    name: 'Amira Fariza',
    teaches: ['PHP'],
    learns: ['SQL'],
  },
  {
    id: 'u3',
    name: 'Adam Faiz',
    teaches: ['Laravel'],
    learns: ['Power BI'],
  },
];

const state = {
  me: {
    id: 'me',
    name: localStorage.getItem('me_name') || 'Ezam Hazami',
    username: localStorage.getItem('me_username') || '@e_zam',
    teaches: (localStorage.getItem('me_teach') || 'Python').split(',').map(s => s.trim()).filter(Boolean),
    learns: (localStorage.getItem('me_learn') || 'JavaScript').split(',').map(s => s.trim()).filter(Boolean),
  },
  queue: [...mockUsers],
  likes: new Set(JSON.parse(localStorage.getItem('likes') || '[]')), // ids liked by me
  passes: new Set(JSON.parse(localStorage.getItem('passes') || '[]')),
  matches: JSON.parse(localStorage.getItem('matches') || '[]'), // [{peerId, messages:[], schedule:null, rating:null}]
  activeChatPeerId: null,
  posts: JSON.parse(localStorage.getItem('user_posts') || '[]'), // additional posts created by users
};

// When navigating to Matches and we want the chat to auto-open
let pendingOpenChatId = null;

// If user has swiped everyone previously, reset the deck on load (prototype convenience)
function resetDeckIfEmptyOnLoad() {
  const remaining = state.queue.filter(u => !state.likes.has(u.id) && !state.passes.has(u.id));
  if (remaining.length === 0) {
    state.likes.clear();
    state.passes.clear();
    localStorage.setItem('likes', JSON.stringify([]));
    localStorage.setItem('passes', JSON.stringify([]));
  }
}

// Helpers
function saveLocal() {
  localStorage.setItem('likes', JSON.stringify(Array.from(state.likes)));
  localStorage.setItem('passes', JSON.stringify(Array.from(state.passes)));
  localStorage.setItem('matches', JSON.stringify(state.matches));
  localStorage.setItem('me_name', state.me.name);
  localStorage.setItem('me_username', state.me.username);
  localStorage.setItem('me_teach', state.me.teaches.join(', '));
  localStorage.setItem('me_learn', state.me.learns.join(', '));
  localStorage.setItem('user_posts', JSON.stringify(state.posts));
}

function $(sel) { return document.querySelector(sel); }
function create(el, cls) { const e = document.createElement(el); if (cls) e.className = cls; return e; }

function getUserById(id) {
  const inMocks = mockUsers.find(u => u.id === id);
  if (inMocks) return inMocks;
  // Allow matching to my own posts by treating posts as user-like objects
  const post = state.posts.find(p => p.id === id);
  if (post) return { id: post.id, name: post.name, teaches: post.teaches, learns: post.learns };
  return null;
}

// Helper function to get current profile teaches data
function getCurrentProfileTeaches() {
  const teachInput = $('#profile-teach');
  if (!teachInput) return state.me.teaches;
  const currentValue = teachInput.value.trim();
  if (!currentValue) return state.me.teaches;
  return currentValue.split(',').map(s => s.trim()).filter(Boolean);
}

// Simple view navigation helper
function navigateTo(view) {
  document.querySelectorAll('.nav-btn').forEach(b => {
    const isTarget = b.getAttribute('data-view') === view;
    b.classList.toggle('active', isTarget);
  });
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  const section = $('#view-' + view);
  if (section) section.classList.add('active');
  if (view === 'matches') renderMatches();
  if (view === 'explore') renderExplore();
  if (view === 'myposts') renderMyPosts();
}

// Username resolver for cards and posts
function getUsernameForUser(user) {
  if (!user) return '@unknown';
  if (user.id === 'u1') return '@Iqbalzzz';
  if (user.id === 'u2') return '@mrfrza';
  if (user.id === 'u3') return '@admfaiz';
  if (user.id === 'me') return state.me.username || '@e_zam';
  // Posts authored by me use id starting with p_
  if (String(user.id || '').startsWith('p_')) return state.me.username || '@e_zam';
  return '@demo';
}

// Navbar
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.nav-btn');
  if (!btn) return;
  // If this nav item is a link (e.g., Home) or lacks a data-view, let the browser handle it
  const view = btn.getAttribute('data-view');
  if (!view) return;
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  const section = $('#view-' + view);
  if (section) section.classList.add('active');
  if (view === 'matches') renderMatches();
  if (view === 'explore') renderExplore();
  if (view === 'profile') renderProfile();
  if (view === 'myposts') renderMyPosts();
});

// Profile view removed - using default teach/learn from localStorage

// Swipe cards (kept for prototype but not primary UI)
function renderCard(user) {
  const tpl = document.getElementById('card-template');
  const node = tpl.content.firstElementChild.cloneNode(true);
  node.querySelector('.card-name').textContent = user.name;
  const teach = node.querySelector('.pill.teach');
  teach.textContent = 'Teaches: ' + user.teaches.join(', ');
  const learn = node.querySelector('.pill.learn');
  learn.textContent = 'Wants: ' + user.learns.join(', ');
  node.querySelector('.report-btn').addEventListener('click', () => reportUser(user));
  enableDrag(node, user);
  return node;
}

function enableDrag(card, user) {
  let startX = 0, startY = 0, currentX = 0, currentY = 0, isDown = false;
  const likeThreshold = 120;

  function onPointerDown(e) {
    isDown = true;
    startX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
    startY = e.clientY || (e.touches && e.touches[0].clientY) || 0;
    card.setPointerCapture?.(e.pointerId || 1);
  }
  function onPointerMove(e) {
    if (!isDown) return;
    const x = e.clientX || (e.touches && e.touches[0].clientX) || 0;
    const y = e.clientY || (e.touches && e.touches[0].clientY) || 0;
    currentX = x - startX; currentY = y - startY;
    const rot = currentX / 20;
    card.style.transform = `translate(${currentX}px, ${currentY}px) rotate(${rot}deg)`;
    card.style.transition = 'none';
  }
  function onPointerUp() {
    if (!isDown) return;
    isDown = false;
    card.style.transition = 'transform 200ms ease';
    if (currentX > likeThreshold) {
      like(user);
      card.style.transform = 'translate(600px, -40px) rotate(20deg)';
      setTimeout(renderStack, 180);
    } else if (currentX < -likeThreshold) {
      pass(user);
      card.style.transform = 'translate(-600px, -40px) rotate(-20deg)';
      setTimeout(renderStack, 180);
    } else {
      card.style.transform = '';
    }
    currentX = currentY = 0;
  }

  card.addEventListener('mousedown', onPointerDown);
  window.addEventListener('mousemove', onPointerMove);
  window.addEventListener('mouseup', onPointerUp);
  card.addEventListener('touchstart', onPointerDown, { passive: true });
  window.addEventListener('touchmove', onPointerMove, { passive: true });
  window.addEventListener('touchend', onPointerUp);
}

// Explore grid rendering
function renderExplore() {
  const grid = $('#explore-grid');
  if (!grid) return;
  const learnFilter = $('#filter-learn').value.trim().toLowerCase();
  grid.innerHTML = '';
  let dataset = mockUsers;
  // merge user posts into dataset
  const mappedPosts = state.posts.map(p => ({ id: p.id, name: p.name, teaches: p.teaches, learns: p.learns }));
  dataset = [...mappedPosts, ...dataset];
  // Remove users I already matched with from Explore
  const matchedIds = new Set(state.matches.map(m => m.peerId));
  dataset = dataset.filter(u => !matchedIds.has(u.id));
  // Show everyone by default. If a learn filter is provided, show users who:
  // 1) teach the desired subject AND 2) want to learn something I teach
  if (learnFilter) {
    dataset = dataset.filter(u =>
      u.teaches.some(s => s.toLowerCase().includes(learnFilter)) &&
      u.learns.some(s => getCurrentProfileTeaches().map(x => x.toLowerCase()).includes(s.toLowerCase()))
    );
  }

  if (!dataset.length) {
    const empty = create('div', 'muted'); empty.textContent = 'No results found.'; grid.appendChild(empty); return;
  }
  const tpl = document.getElementById('explore-item-template');
  dataset.forEach(u => {
    const node = tpl.content.firstElementChild.cloneNode(true);
    node.querySelector('.course-title').textContent = `${u.name} — ${u.teaches[0]} Bootcamp`;
    node.querySelector('.course-desc').textContent = `Learn ${u.teaches.join(', ')} with ${u.name}.`;
    node.querySelector('.teaches').textContent = `Teaches: ${u.teaches.join(', ')}`;
    node.querySelector('.learns').textContent = `Wants to learn: ${u.learns.join(', ')}`;
    // Show a friendlier label for my own posts
    if (String(u.id || '').startsWith('p_')) {
      node.querySelector('.posted').textContent = 'Post by you';
    } else {
      node.querySelector('.posted').textContent = `Posted by ${getUsernameForUser(u)}`;
    }
    const btn = node.querySelector('.match-btn');
    // If this is my own post (id like p_123), do not allow matching
    if (String(u.id || '').startsWith('p_')) {
      btn.textContent = 'Waiting for matches';
      btn.disabled = true;
      btn.classList.remove('primary');
      btn.classList.add('ghost');
      grid.appendChild(node);
      return;
    }
    const already = !!getMatch(u.id);
    if (already) {
      btn.textContent = 'Chat';
      btn.addEventListener('click', () => { pendingOpenChatId = u.id; navigateTo('matches'); });
    } else {
      btn.textContent = 'Match';
      btn.addEventListener('click', () => {
        // only create match when overlap exists; otherwise inform user
        // Get current profile data instead of stored data
        const currentTeaches = getCurrentProfileTeaches();
        // Match only if: they want to learn something I can teach
        const overlap = u.learns.some(s => currentTeaches.includes(s));
        if (!overlap) {
          alert('No match yet. You don\'t have compatible teaching/learning interests.');
          return;
        }
        like(u); // will create a match if overlap
        renderExplore(); // remove matched card from explore
      });
    }
    grid.appendChild(node);
  });

}

function renderMyPosts() {
  const myPostsGrid = $('#my-posts');
  if (!myPostsGrid) return;
  myPostsGrid.innerHTML = '';
  if (!state.posts.length) {
    const empty = create('div', 'muted'); empty.textContent = 'No posts yet.'; myPostsGrid.appendChild(empty);
    return;
  }
  state.posts.forEach(p => {
    const card = create('div', 'course-card');
    const title = create('div', 'course-title'); title.textContent = `${p.name} — ${p.teaches[0] || 'Teaching'}`; card.appendChild(title);
    const desc = create('div', 'course-desc muted'); desc.textContent = `Teaches ${p.teaches.join(', ')} · Wants ${p.learns.join(', ')}`; card.appendChild(desc);
    const status = create('div', 'muted'); status.textContent = 'Status: Waiting for matches'; card.appendChild(status);
    const actions = create('div', 'post-actions');
    const delBtn = create('button', 'danger ghost'); delBtn.textContent = 'Delete';
    actions.appendChild(delBtn); card.appendChild(actions);
    delBtn.addEventListener('click', () => deletePost(p.id));
    myPostsGrid.appendChild(card);
  });
}

// Filter controls
document.addEventListener('click', (e) => {
  if (e.target && e.target.id === 'filter-apply') renderExplore();
  if (e.target && e.target.id === 'filter-clear') {
    const learn = $('#filter-learn');
    if (learn) learn.value = '';
    renderExplore();
  }
});

function prefillExploreFiltersFromProfile() {
  const learnInput = $('#filter-learn');
  if (!learnInput) return;
  // Leave blank so Explore initially shows everyone
  learnInput.value = '';
}

// Profile view: initialize inputs
function renderProfile() {
  const teach = $('#profile-teach');
  if (!teach) return;
  teach.value = state.me.teaches.join(', ');
}

// Save profile
document.addEventListener('click', (e) => {
  if (e.target && e.target.id === 'profile-save') {
    const t = ($('#profile-teach').value || '').split(',').map(s => s.trim()).filter(Boolean);
    state.me.teaches = t.length ? t : state.me.teaches;
    saveLocal();
    alert('Profile saved. Explore uses your teaching skills from here.');
  }
});

// New Post modal interactions
document.addEventListener('click', (e) => {
  if (e.target && e.target.id === 'btn-new-post') {
    $('#post-modal').classList.remove('hidden');
    $('#post-learn').value = '';
  }
  if (e.target && e.target.id === 'post-cancel') {
    $('#post-modal').classList.add('hidden');
  }
  if (e.target && e.target.id === 'post-submit') {
    submitPostForm();
  }
});

function submitPostForm(editingId) {
  const name = state.me.name || 'Ezam Hazami';
  const teaches = state.me.teaches; // from profile
  const learns = $('#post-learn').value.split(',').map(s => s.trim()).filter(Boolean);
  if (!teaches.length) { alert('Please add at least one subject you can teach in Profile.'); return; }
  if (editingId) {
    const idx = state.posts.findIndex(p => p.id === editingId);
    if (idx >= 0) state.posts[idx] = { id: editingId, name, teaches, learns };
  } else {
    const id = 'p_' + Date.now();
    state.posts.unshift({ id, name, teaches, learns });
  }
  saveLocal();
  $('#post-modal').classList.add('hidden');
  renderExplore();
}

// Edit functionality removed per requirements

function deletePost(postId) {
  if (!confirm('Delete this post?')) return;
  state.posts = state.posts.filter(p => p.id !== postId);
  saveLocal();
  renderExplore();
  renderMyPosts();
}

function getTopUser() {
  const remaining = state.queue.filter(u => !state.likes.has(u.id) && !state.passes.has(u.id));
  return remaining[0];
}

function pass(user) { state.passes.add(user.id); saveLocal(); }

function like(user) {
  state.likes.add(user.id);
  saveLocal();
  // Determine match: they want to learn something I teach
  // Use current profile data instead of stored data
  const currentTeaches = getCurrentProfileTeaches();
  const overlap = user.learns.some(subject => currentTeaches.includes(subject));
  if (overlap) {
    makeMatch(user);
    showMatchModal(user, overlap);
  }
}

function makeMatch(user) {
  if (!state.matches.find(m => m.peerId === user.id)) {
    state.matches.push({ peerId: user.id, messages: [], schedule: null, rating: null });
    saveLocal();
    renderMatches();
  }
}

// Match modal
const matchModal = $('#match-modal');
const matchDesc = $('#match-desc');
let pendingMatchUser = null;
function showMatchModal(user) {
  pendingMatchUser = user;
  const currentTeaches = getCurrentProfileTeaches();
  const overlap = user.learns.filter(s => currentTeaches.includes(s));
  let matchText = '';
  if (overlap.length > 0) {
    matchText = `you want to learn ${overlap.join(', ')} from ${user.name}`;
  }
  matchDesc.textContent = `${matchText} — start chatting!`;
  matchModal.classList.remove('hidden');
}
$('#close-match').addEventListener('click', () => { matchModal.classList.add('hidden'); pendingMatchUser = null; });
$('#open-chat').addEventListener('click', () => {
  if (!pendingMatchUser) return;
  // Navigate to matches view; renderMatches will auto-open
  pendingOpenChatId = pendingMatchUser.id;
  navigateTo('matches');
  matchModal.classList.add('hidden');
  pendingMatchUser = null;
});

// Matches list
function renderMatches() {
  const list = $('#matches-list');
  list.innerHTML = '';
  if (!state.matches.length) {
    const empty = create('div', 'muted'); empty.textContent = 'No matches yet — start searching!'; list.appendChild(empty); return;
  }
  const tpl = document.getElementById('match-item-template');
  state.matches.forEach(m => {
    const user = getUserById(m.peerId); if (!user) return;
    const node = tpl.content.firstElementChild.cloneNode(true);
    node.querySelector('.match-name').textContent = user.name;
    node.querySelector('.match-desc').textContent = `Teaches ${user.teaches.join(', ')} · Wants ${user.learns.join(', ')}`;
    node.querySelector('.open-chat').addEventListener('click', () => openChat(user.id));
    node.querySelector('.report').addEventListener('click', () => reportUser(user));
    list.appendChild(node);
  });
  // If there is a pending chat to open, do it now that the DOM is ready
  if (pendingOpenChatId) {
    const toOpen = pendingOpenChatId; pendingOpenChatId = null;
    setTimeout(() => openChat(toOpen), 20);
  }
}

// Report
function reportUser(user) {
  alert(`Reported ${user.name}. Our team will review.`);
}
$('#report-in-chat').addEventListener('click', () => {
  if (!state.activeChatPeerId) return; const user = getUserById(state.activeChatPeerId); if (!user) return; reportUser(user);
});

// Chat
function openChat(peerId) {
  state.activeChatPeerId = peerId;
  const user = getUserById(peerId);
  const drawer = $('#chat-drawer');
  drawer.classList.remove('hidden');
  // trigger slide-in animation
  requestAnimationFrame(() => drawer.classList.add('open'));
  $('#chat-name').textContent = user.name;
  $('#chat-sub').textContent = `Teaches ${user.teaches.join(', ')} · Wants ${user.learns.join(', ')}`;
  renderChat();
}
$('#close-chat').addEventListener('click', () => { const d = $('#chat-drawer'); d.classList.remove('open'); setTimeout(() => d.classList.add('hidden'), 220); state.activeChatPeerId = null; });

function getMatch(peerId) { return state.matches.find(m => m.peerId === peerId); }

// Scheduling helpers
function getLocalMinDatetime() {
  const d = new Date();
  d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
  return d.toISOString().slice(0, 16);
}

function proposeAlternateTime(previousWhen) {
  const base = new Date(previousWhen || Date.now());
  const alt = new Date(base.getTime() + 60 * 60 * 1000);
  return alt.toISOString();
}

function renderChat() {
  const match = getMatch(state.activeChatPeerId); if (!match) return;
  const msgBox = $('#chat-messages');
  msgBox.innerHTML = '';
  match.messages.forEach(msg => {
    if (msg.type === 'proposal') {
      const wrap = create('div', 'msg ' + (msg.from === 'me' ? 'me' : 'them'));
      const info = create('div');
      info.textContent = `${msg.text}${msg.status === 'approved' ? ' (approved)' : ''}`;
      wrap.appendChild(info);
      if (msg.status !== 'approved' && msg.from !== 'me') {
        const approveBtn = create('button', 'primary');
        approveBtn.textContent = 'Approve';
        approveBtn.addEventListener('click', () => {
          msg.status = 'approved';
          match.schedule = msg.when;
          match.messages.push({ from: 'system', type: 'system', text: `Session approved for ${new Date(msg.when).toLocaleString()}.`, ts: Date.now() });
          saveLocal();
          renderChat();
        });
        wrap.appendChild(approveBtn);

        const declineBtn = create('button', 'ghost');
        declineBtn.textContent = 'Decline & Propose New';
        declineBtn.addEventListener('click', () => {
          msg.status = 'declined';
          const newWhen = proposeAlternateTime(msg.when);
          const newText = `Proposed session: ${new Date(newWhen).toLocaleString()}`;
          match.messages.push({ type: 'proposal', from: 'them', text: newText, when: newWhen, status: 'pending', ts: Date.now() });
          match.messages.push({ from: 'system', type: 'system', text: 'Your proposal was declined. A new time has been suggested.', ts: Date.now() });
          saveLocal();
          renderChat();
        });
        wrap.appendChild(declineBtn);
      }
      msgBox.appendChild(wrap);
    } else if (msg.type === 'feedback') {
      const bubble = create('div', 'msg system');
      bubble.textContent = msg.text;
      msgBox.appendChild(bubble);
    } else if (msg.type === 'system') {
      const bubble = create('div', 'msg system');
      bubble.textContent = msg.text;
      msgBox.appendChild(bubble);
    } else {
      const bubble = create('div', 'msg ' + (msg.from === 'me' ? 'me' : 'them'));
      bubble.textContent = msg.text;
      msgBox.appendChild(bubble);
    }
  });
  msgBox.scrollTop = msgBox.scrollHeight;

  // scheduling
  const schedInput = $('#schedule-datetime');
  const schedLabel = $('#scheduled-label');
  if (match.schedule) {
    schedLabel.textContent = `Scheduled: ${new Date(match.schedule).toLocaleString()}`;
  } else {
    schedLabel.textContent = '';
  }

  // Prevent selecting past dates and set pending button state
  if (schedInput) {
    schedInput.min = getLocalMinDatetime();
  }
  const sendBtn = $('#schedule-save');
  const hasPendingMine = match.messages.some(m => m.type === 'proposal' && m.from === 'me' && m.status === 'pending');
  if (sendBtn) {
    if (hasPendingMine) {
      sendBtn.textContent = 'Waiting approval';
      sendBtn.classList.remove('secondary');
      sendBtn.classList.add('warning');
      sendBtn.disabled = true;
    } else {
      sendBtn.textContent = 'Send Request';
      sendBtn.classList.remove('warning');
      sendBtn.classList.add('secondary');
      sendBtn.disabled = false;
    }
  }

}

$('#chat-send').addEventListener('click', sendMessage);
$('#chat-text').addEventListener('keydown', (e) => { if (e.key === 'Enter') sendMessage(); });

function sendMessage() {
  const input = $('#chat-text');
  const text = input.value.trim();
  if (!text || !state.activeChatPeerId) return;
  const match = getMatch(state.activeChatPeerId); if (!match) return;
  match.messages.push({ from: 'me', text, ts: Date.now() });
  // simulate reply
  setTimeout(() => {
    match.messages.push({ from: 'them', text: 'Sounds good! ', ts: Date.now() });
    saveLocal();
    if (state.activeChatPeerId) renderChat();
  }, 600);
  input.value = '';
  saveLocal();
  renderChat();
}

// Scheduling via proposal message
$('#schedule-save').addEventListener('click', () => {
  if (!state.activeChatPeerId) return;
  const when = $('#schedule-datetime').value;
  const match = getMatch(state.activeChatPeerId); if (!match) return;
  if (!when) { alert('Pick a date/time.'); return; }
  // Validate not past
  const chosen = new Date(when);
  if (chosen.getTime() < Date.now()) { alert('Please choose a future date and time.'); return; }
  const text = `Proposed session: ${new Date(when).toLocaleString()}`;
  match.messages.push({ type: 'proposal', from: 'me', text, when, status: 'pending', ts: Date.now() });
  match.messages.push({ from: 'system', type: 'system', text: 'You requested a session. Waiting for approval…', ts: Date.now() });
  saveLocal();
  renderChat();
});

// Matches list refresh when navigating
document.addEventListener('click', (e) => {
  if (e.target.closest('[data-view="matches"]')) {
    renderMatches();
  }
});

// Clear old localStorage data to force update
localStorage.removeItem('me_teach');
localStorage.removeItem('me_learn');

// Clear matches and likes for demo purposes (remove this line when you want to keep matches)
localStorage.removeItem('likes');
localStorage.removeItem('passes');
localStorage.removeItem('matches');

// Feedback functionality
let currentFeedbackRating = 0;

// Initialize feedback stars
function initFeedbackStars() {
  const wrap = $('#feedback-stars');
  wrap.innerHTML = '';
  for (let i = 1; i <= 5; i++) {
    const s = create('span', 'star' + (i <= currentFeedbackRating ? ' active' : ''));
    s.textContent = '★';
    s.dataset.value = String(i);
    s.addEventListener('click', () => setFeedbackStar(i));
    wrap.appendChild(s);
  }
}

function setFeedbackStar(val) {
  currentFeedbackRating = val;
  initFeedbackStars();
}

// Feedback modal interactions
document.addEventListener('click', (e) => {
  if (e.target && e.target.id === 'feedback-btn') {
    currentFeedbackRating = 0;
    $('#feedback-comment').value = '';
    initFeedbackStars();
    $('#feedback-modal').classList.remove('hidden');
  }
  if (e.target && e.target.id === 'feedback-cancel') {
    $('#feedback-modal').classList.add('hidden');
  }
  if (e.target && e.target.id === 'feedback-submit') {
    submitFeedback();
  }
});

function submitFeedback() {
  const comment = $('#feedback-comment').value.trim();
  const rating = currentFeedbackRating;
  
  if (!rating) {
    alert('Please select a star rating.');
    return;
  }
  
  if (!state.activeChatPeerId) {
    alert('No active chat found.');
    return;
  }
  
  const match = getMatch(state.activeChatPeerId);
  if (!match) {
    alert('No match found.');
    return;
  }
  
  // Store feedback in the match
  match.feedback = {
    rating: rating,
    comment: comment,
    timestamp: Date.now()
  };
  
  saveLocal();
  $('#feedback-modal').classList.add('hidden');
  
  // Show success message
  alert(`Thank you for your feedback! You rated ${rating} star${rating > 1 ? 's' : ''}.`);
  
  // Add feedback message to chat
  const user = getUserById(state.activeChatPeerId);
  const feedbackMsg = `You gave ${user.name} a ${rating}-star rating${comment ? ' with a comment' : ''}.`;
  match.messages.push({ 
    from: 'system', 
    text: feedbackMsg, 
    ts: Date.now(),
    type: 'feedback'
  });
  
  saveLocal();
  renderChat();
}

// Init
resetDeckIfEmptyOnLoad();
prefillExploreFiltersFromProfile();
renderExplore();
renderMatches();
renderMyPosts();


