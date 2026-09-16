(() => {
    const input = document.getElementById('profile-photo-input');
    const openButton = document.getElementById('profile-photo-button');
    const modal = document.getElementById('crop-modal');
    const cropImage = document.getElementById('crop-image');
    const avatarPreview = document.getElementById('profile-avatar-preview');
    const output = document.getElementById('cropped-image-data');
    const closeButton = document.getElementById('crop-close');
    const cancelButton = document.getElementById('crop-cancel');
    const applyButton = document.getElementById('crop-apply');

    if (!input || !openButton || !modal || !cropImage || !avatarPreview || !output || typeof Cropper === 'undefined') return;

    let cropper = null;
    let objectUrl = null;

    function destroyCropper() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
    }

    function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        destroyCropper();
        input.value = '';
    }

    function openEditor(file) {
        if (!file) return;
        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            window.alert('Please choose a JPG, PNG, or WEBP image.');
            input.value = '';
            return;
        }
        if (file.size > 8 * 1024 * 1024) {
            window.alert('Please choose an image smaller than 8 MB.');
            input.value = '';
            return;
        }

        destroyCropper();
        objectUrl = URL.createObjectURL(file);
        cropImage.src = objectUrl;
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        cropImage.onload = () => {
            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.9,
                responsive: true,
                restore: false,
                guides: false,
                center: true,
                highlight: false,
                background: false,
                movable: true,
                zoomable: true,
                rotatable: true,
                scalable: false,
                toggleDragModeOnDblclick: false
            });
        };
    }

    openButton.addEventListener('click', () => input.click());
    input.addEventListener('change', () => openEditor(input.files && input.files[0]));
    closeButton.addEventListener('click', closeModal);
    cancelButton.addEventListener('click', closeModal);

    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('open')) closeModal();
    });

    document.querySelectorAll('[data-crop-action]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!cropper) return;
            switch (button.dataset.cropAction) {
                case 'zoom-in': cropper.zoom(0.12); break;
                case 'zoom-out': cropper.zoom(-0.12); break;
                case 'rotate-left': cropper.rotate(-90); break;
                case 'rotate-right': cropper.rotate(90); break;
                case 'reset': cropper.reset(); break;
            }
        });
    });

    applyButton.addEventListener('click', () => {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({
            width: 512,
            height: 512,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
            fillColor: '#ffffff'
        });
        if (!canvas) return;

        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        output.value = dataUrl;
        avatarPreview.src = dataUrl;
        closeModal();
    });
})();
