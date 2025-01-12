function showOnlyBlock(blockId, blockClass) {
    const blocks = document.querySelectorAll(`.${blockClass}`);
    blocks.forEach(block => {
        block.style.display = block.id === blockId ? 'block' : 'none';
    });
}

function toggleModal(modalId, isVisible) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = isVisible ? "flex" : "none";
    }
}

export { showOnlyBlock, toggleModal }