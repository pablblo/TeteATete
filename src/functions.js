function showOnlyBlock(blockId, blockClass) {
    const blocks = document.querySelectorAll(`.${blockClass}`);
    blocks.forEach(block => {
        block.style.display = block.id === blockId ? 'block' : 'none';
    });
}

export { showOnlyBlock }