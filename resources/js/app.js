import 'bootstrap';

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    const reviewRow = event.target.closest('[data-review-url]');

    if (
        !reviewRow
        || event.target.closest('a, button, input, select, textarea, label, [data-review-selection-cell]')
    ) {
        return;
    }

    window.location.assign(reviewRow.dataset.reviewUrl);
});

document.querySelectorAll('[data-review-selection-form]').forEach((selectionForm) => {
    const selectAllCheckbox = selectionForm.querySelector('[data-review-select-all]');
    const articleCheckboxes = [...selectionForm.querySelectorAll('[data-review-checkbox]')];
    const selectionCount = selectionForm.querySelector('[data-review-selection-count]');
    const actionButtons = selectionForm.querySelectorAll('[data-review-action]');

    const updateSelectionState = () => {
        const selectedCount = articleCheckboxes.filter((checkbox) => checkbox.checked).length;

        if (selectionCount) {
            selectionCount.textContent = selectedCount;
        }

        actionButtons.forEach((button) => {
            button.disabled = selectedCount === 0;
        });

        if (selectAllCheckbox) {
            selectAllCheckbox.checked = selectedCount === articleCheckboxes.length;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < articleCheckboxes.length;
        }
    };

    articleCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', updateSelectionState);
    });

    selectAllCheckbox?.addEventListener('change', () => {
        articleCheckboxes.forEach((checkbox) => {
            checkbox.checked = selectAllCheckbox.checked;
        });

        updateSelectionState();
    });

    updateSelectionState();
});
