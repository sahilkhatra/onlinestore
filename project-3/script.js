const openDatabaseButtons = document.querySelectorAll('[data-database-target]')
const closeDatabaseButtons = document.querySelectorAll('[data-close-button]')
const overlay = document.getElementById('overlay');

openDatabaseButtons.forEach(button => {
    button.addEventListener('click', () => {
        const database = document.querySelector(button.dataset.databaseTarget)
        openDatabase(database)
    })
})

overlay.addEventListener('click', () => {
    const database = document.querySelectorAll('.database.active')
    database.forEach(database => {
        closeDatabase(database)
    })
})

closeDatabaseButtons.forEach(button => {
    button.addEventListener('click', () => {
        const database = button.closest('.database')
        closeDatabase(database)
    })
})


function openDatabase(database) {
    if (database == null) return;
    database.classList.add('active')
    overlay.classList.add('active')
}

function closeDatabase(database) {
    if (database == null) return;
    database.classList.remove('active')
    overlay.classList.remove('active')
}
