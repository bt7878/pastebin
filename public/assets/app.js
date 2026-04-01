function validateForm() {
    const text = document.querySelector("#text");

    if (text.value.trim() === "") {
        text.setCustomValidity('Please enter some text.');
        text.reportValidity();
        return false;
    }

    return true;
}
