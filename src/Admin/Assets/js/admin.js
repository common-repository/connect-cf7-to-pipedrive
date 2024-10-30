window.onload = function () {
    const selectElements = document.querySelectorAll('select');
    selectElements.forEach(function (selectElement) {
        selectElement.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const optgroupLabel = selectedOption.parentNode.label;
            const parentSelect = this.parentNode;

            // Remove existing appended label if exists
            let existingLabel = parentSelect.querySelector('.appended-label');
            if (existingLabel) {
                existingLabel.remove();
            }

            // Append new label
            const label = document.createElement('label');
            label.className = 'appended-label';
            label.textContent = optgroupLabel;
            parentSelect.appendChild(label);
        });

        // Trigger change event
        const event = new Event('change');
        selectElement.dispatchEvent(event);
    });
}