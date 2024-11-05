class Bulletinboard {
    static init() {
        Bulletinboard.initPreviews();
    }

    static initPreviews() {
        document.querySelectorAll('.bulletinboard-preview').forEach((previewElement) => {
            previewElement.querySelectorAll('.delete').forEach((deleteLink) => {
                deleteLink.addEventListener('click', (event) => {
                    event.preventDefault();

                    previewElement.closest('.input').querySelectorAll('[data-index="' +
                        previewElement.dataset.index + '"]').forEach((element) => {
                            element.remove();
                    });
                });
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', Bulletinboard.init);
