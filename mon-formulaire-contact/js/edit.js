function openPopup(index, title, email) {
    document.querySelector('#editForm [name="mfc_form_index"]').value = index;
    document.querySelector('#editForm [name="mfc_new_title"]').value = title;
    document.querySelector('#editForm [name="mfc_new_email"]').value = email;
    document.getElementById('editFormPopup').style.display = 'flex';
}

function closePopup() {
    document.getElementById('editFormPopup').style.display = 'none';
}
