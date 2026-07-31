// API Base URL
const API_URL = 'http://160.25.222.15:8080/backend';

// Current state
let currentPage = 1;
let currentCategory = '';

// Initialize app
document.addEventListener('DOMContentLoaded', function() {
    checkAuth();
    loadFeaturedBooks();
});

// Check authentication status
async function checkAuth() {
    try {
        const response = await fetch(`${API_URL}/auth.php?action=check`, {
            credentials: 'include'
        });
        const data = await response.json();

        if (data.authenticated) {
            const loginLink = document.getElementById('loginLink');
            const logoutLink = document.getElementById('logoutLink');
            const myBorrowingsLink = document.getElementById('myBorrowingsLink');
            const adminLink = document.getElementById('adminLink');

            if (loginLink) loginLink.style.display = 'none';
            if (logoutLink) logoutLink.style.display = 'block';
            if (myBorrowingsLink) myBorrowingsLink.style.display = 'block';

            if (data.user.role === 'admin' || data.user.role === 'librarian') {
                if (adminLink) adminLink.style.display = 'block';
            }
        } else {
            const loginLink = document.getElementById('loginLink');
            const logoutLink = document.getElementById('logoutLink');
            const myBorrowingsLink = document.getElementById('myBorrowingsLink');
            const adminLink = document.getElementById('adminLink');

            if (loginLink) loginLink.style.display = 'block';
            if (logoutLink) logoutLink.style.display = 'none';
            if (myBorrowingsLink) myBorrowingsLink.style.display = 'none';
            if (adminLink) adminLink.style.display = 'none';
        }
    } catch (error) {
        console.error('Auth check error:', error);
    }
}

// Logout
async function logout() {
    try {
        await fetch(`${API_URL}/auth.php?action=logout`, {
            credentials: 'include'
        });

        localStorage.removeItem('user');
        window.location.href = 'index.html';
    } catch (error) {
        console.error('Logout error:', error);
    }
}

// Load featured books
async function loadFeaturedBooks() {
    try {
        const response = await fetch(`${API_URL}/api.php?limit=6`);
        const data = await response.json();

        if (data.success) {
            displayBooks(data.data, 'featuredBooks');
        }
    } catch (error) {
        console.error('Error loading featured books:', error);
    }
}

// Show books section
async function showBooks() {
    hideAllSections();
    document.getElementById('booksSection').style.display = 'block';

    // Load categories for filter
    await loadCategories();

    // Load books
    loadBooks();
}

// Load books with pagination
async function loadBooks(page = 1) {
    try {
        currentPage = page;
        const categoryParam = currentCategory ? `&category=${currentCategory}` : '';
        const response = await fetch(`${API_URL}/api.php?page=${page}&limit=12${categoryParam}`);
        const data = await response.json();

        if (data.success) {
            displayBooks(data.data, 'booksList');
            displayPagination(data.pagination);
        }
    } catch (error) {
        console.error('Error loading books:', error);
    }
}

// Display books
function displayBooks(books, containerId) {
    const container = document.getElementById(containerId);

    if (books.length === 0) {
        container.innerHTML = '<p>No books found.</p>';
        return;
    }

    container.innerHTML = books.map(book => `
        <div class="book-card" onclick="showBookDetail(${book.id})">
            <div class="book-cover">
                <i class="fas fa-book"></i>
            </div>
            <h3>${book.title}</h3>
            <p class="author">by ${book.author}</p>
            <span class="category">${book.category}</span>
            <p class="availability ${book.available_copies > 0 ? 'available' : 'unavailable'}">
                ${book.available_copies > 0 ? `${book.available_copies} available` : 'Not available'}
            </p>
        </div>
    `).join('');
}

// Display pagination
function displayPagination(pagination) {
    const container = document.getElementById('pagination');
    const pages = [];

    // Previous button
    pages.push(`
        <button onclick="loadBooks(${pagination.page - 1})" ${pagination.page === 1 ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i> Previous
        </button>
    `);

    // Page numbers
    for (let i = 1; i <= pagination.pages; i++) {
        pages.push(`
            <button onclick="loadBooks(${i})" class="${i === pagination.page ? 'active' : ''}">
                ${i}
            </button>
        `);
    }

    // Next button
    pages.push(`
        <button onclick="loadBooks(${pagination.page + 1})" ${pagination.page === pagination.pages ? 'disabled' : ''}>
            Next <i class="fas fa-chevron-right"></i>
        </button>
    `);

    container.innerHTML = pages.join('');
}

// Show book detail
async function showBookDetail(bookId) {
    try {
        const response = await fetch(`${API_URL}/api.php?action=book&id=${bookId}`);
        const data = await response.json();

        if (data.success) {
            const book = data.data;
            const detailHtml = `
                <h2>${book.title}</h2>
                <p><strong>Author:</strong> ${book.author}</p>
                <p><strong>ISBN:</strong> ${book.isbn}</p>
                <p><strong>Publisher:</strong> ${book.publisher}</p>
                <p><strong>Published Year:</strong> ${book.publish_year}</p>
                <p><strong>Category:</strong> ${book.category}</p>
                <p><strong>Available Copies:</strong> ${book.available_copies} / ${book.total_copies}</p>
                <p><strong>Description:</strong> ${book.description || 'No description available.'}</p>
                ${book.available_copies > 0 ? `<button class="btn-primary" onclick="borrowBook(${book.id})">Borrow This Book</button>` : ''}
            `;

            document.getElementById('bookDetail').innerHTML = detailHtml;
            document.getElementById('bookModal').style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading book detail:', error);
    }
}

// Close book modal
function closeBookModal() {
    document.getElementById('bookModal').style.display = 'none';
}

// Borrow book
async function borrowBook(bookId) {
    try {
        const response = await fetch(`${API_URL}/api.php?action=borrow`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ book_id: bookId })
        });

        const data = await response.json();

        if (data.success) {
            alert('Book borrowed successfully!');
            closeBookModal();
            loadBooks(currentPage);
        } else {
            alert(data.message || 'Failed to borrow book');
        }
    } catch (error) {
        console.error('Error borrowing book:', error);
        alert('Please login to borrow books');
    }
}

// Show categories section
async function showCategories() {
    hideAllSections();
    document.getElementById('categoriesSection').style.display = 'block';

    await loadCategoriesList();
}

// Load categories list
async function loadCategoriesList() {
    try {
        const response = await fetch(`${API_URL}/api.php?action=categories`);
        const data = await response.json();

        if (data.success) {
            const container = document.getElementById('categoriesList');
            container.innerHTML = data.data.map(category => `
                <div class="category-card" onclick="filterByCategory('${category}')">
                    <i class="fas fa-book-open"></i>
                    <h3>${category}</h3>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Load categories for filter dropdown
async function loadCategories() {
    try {
        const response = await fetch(`${API_URL}/api.php?action=categories`);
        const data = await response.json();

        if (data.success) {
            const select = document.getElementById('categoryFilter');
            select.innerHTML = '<option value="">All Categories</option>' +
                data.data.map(cat => `<option value="${cat}">${cat}</option>`).join('');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Filter by category
function filterByCategory(category = null) {
    if (category === null) {
        category = document.getElementById('categoryFilter').value;
    } else {
        // Coming from category card
        showBooks();
        document.getElementById('categoryFilter').value = category;
    }

    currentCategory = category;
    loadBooks(1);
}

// Show my borrowings
async function showMyBorrowings() {
    hideAllSections();
    document.getElementById('borrowingsSection').style.display = 'block';

    try {
        const response = await fetch(`${API_URL}/api.php?action=borrowings`, {
            credentials: 'include'
        });
        const data = await response.json();

        if (data.success) {
            const container = document.getElementById('borrowingsList');

            if (data.data.length === 0) {
                container.innerHTML = '<p>You have no borrowing records.</p>';
                return;
            }

            container.innerHTML = data.data.map(borrowing => {
                const statusClass = borrowing.status === 'overdue' ? 'unavailable' : 'available';
                return `
                    <div class="borrowing-item">
                        <h4>${borrowing.title}</h4>
                        <p><strong>Author:</strong> ${borrowing.author}</p>
                        <p><strong>ISBN:</strong> ${borrowing.isbn}</p>
                        <p><strong>Borrow Date:</strong> ${borrowing.borrow_date}</p>
                        <p><strong>Due Date:</strong> ${borrowing.due_date}</p>
                        ${borrowing.return_date ? `<p><strong>Return Date:</strong> ${borrowing.return_date}</p>` : ''}
                        <p class="${statusClass}"><strong>Status:</strong> ${borrowing.status.toUpperCase()}</p>
                    </div>
                `;
            }).join('');
        } else {
            document.getElementById('borrowingsList').innerHTML = `<p>${data.message}</p>`;
        }
    } catch (error) {
        console.error('Error loading borrowings:', error);
    }
}

// Search books - FLAG 1: SQL Injection vulnerability
async function searchBooks() {
    const query = document.getElementById('searchInput').value;

    if (!query) {
        alert('Please enter a search term');
        return;
    }

    hideAllSections();
    document.getElementById('searchResultsSection').style.display = 'block';

    try {
        // Intentionally vulnerable to SQL injection
        const response = await fetch(`${API_URL}/api.php?action=search&q=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (data.success) {
            displayBooks(data.data, 'searchResults');
        } else {
            document.getElementById('searchResults').innerHTML = `<p>Error: ${data.message}</p>`;
        }
    } catch (error) {
        console.error('Search error:', error);
        document.getElementById('searchResults').innerHTML = '<p>Search error occurred.</p>';
    }
}

// Hide all sections
function hideAllSections() {
    document.getElementById('welcomeSection').style.display = 'none';
    document.getElementById('booksSection').style.display = 'none';
    document.getElementById('categoriesSection').style.display = 'none';
    document.getElementById('borrowingsSection').style.display = 'none';
    document.getElementById('searchResultsSection').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('bookModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
