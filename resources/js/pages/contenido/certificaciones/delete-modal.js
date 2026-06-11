export function initCertificationsDeleteModal(context) {
    var root = context && context.root ? context.root : document;
    var api = context && context.api ? context.api : null;
    var modal = root.querySelector('[data-certifications-delete-modal]');
    var form = modal ? modal.querySelector('[data-certifications-delete-form]') : null;
    var idInput = modal ? modal.querySelector('[data-certifications-delete-id]') : null;
    var targetLabel = modal ? modal.querySelector('[data-certifications-delete-target]') : null;
    var cancelButton = modal ? modal.querySelector('[data-certifications-delete-cancel]') : null;
    var submitButton = modal ? modal.querySelector('[data-certifications-delete-submit]') : null;
    var errorBox = modal ? modal.querySelector('[data-certifications-delete-error]') : null;

    if (!modal || !form || !idInput || !targetLabel || !cancelButton || !submitButton || !errorBox || !api) {
        return;
    }

    var isSubmitting = false;

    function getCookie(name) {
        return typeof api.getCookie === 'function' ? api.getCookie(name) : null;
    }

    function showModalError(message) {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    }

    function hideModalError() {
        errorBox.textContent = '';
        errorBox.classList.add('hidden');
    }

    function setSubmittingState(submitting) {
        isSubmitting = submitting;
        submitButton.disabled = submitting;
        cancelButton.disabled = submitting;
    }

    function openModal(data) {
        hideModalError();
        idInput.value = data.id || '';
        targetLabel.textContent = data.name || 'esta certificacion';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        if (isSubmitting) {
            return;
        }

        form.reset();
        hideModalError();
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    async function deleteCertification(id) {
        var xsrfToken = getCookie('XSRF-TOKEN');

        var response = await fetch('/api/v1/certifications/' + id, {
            method: 'DELETE',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': xsrfToken || '',
            },
        });

        var data = await response.json().catch(function () {
            return {};
        });

        if (!response.ok) {
            throw new Error(data.error || data.message || 'No se pudo eliminar la certificacion.');
        }
    }

    root.addEventListener('click', function (event) {
        var target = event.target;
        if (target.hasAttribute('data-certifications-delete')) {
            openModal({
                id: target.getAttribute('data-certification-id'),
                name: target.getAttribute('data-certification-name')
            });
        }
    });

    cancelButton.addEventListener('click', closeModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (isSubmitting) {
            return;
        }

        var id = idInput.value;

        if (!id) {
            showModalError('No se pudo identificar el registro a eliminar.');
            return;
        }

        hideModalError();
        setSubmittingState(true);

        try {
            await deleteCertification(id);

            if (typeof api.hideError === 'function') {
                api.hideError();
            }

            if (typeof api.reload === 'function') {
                await api.reload();
            }
        } catch (error) {
            showModalError(error.message || 'No se pudo eliminar la certificacion.');

            if (typeof api.showError === 'function') {
                api.showError(error.message || 'No se pudo eliminar la certificacion.');
            }
        } finally {
            setSubmittingState(false);
            closeModal();
        }
    });
}