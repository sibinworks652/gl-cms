<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<div class="toast-container position-fixed end-0 top-0 p-3" style="z-index: 1080;">
    <div id="adminLiveToast" class="toast border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <div class="auth-logo me-auto">
                {{-- <img class="logo-dark" src="{{ asset('admin/assets/images/logo-dark.png') }}" alt="logo-dark" height="18"> --}}
                {{-- <img class="logo-light" src="{{ asset('admin/assets/images/logo-light.png') }}" alt="logo-light" height="18"> --}}
            </div>
            <small class="text-muted" id="adminToastTime">just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="adminToastBody"></div>
    </div>
</div>
<style>
    div:where(.swal2-container) .swal2-html-container{
        padding: 0 !important;
    }
    div:where(.swal2-container) div:where(.swal2-actions){
        margin: 0 auto !important;
    }
    .swal2-popup .swal2-actions{
        margin: 0 !important;
    }
    div:where(.swal2-icon){
            margin: 0.5em auto -2em !important;
    }
    div:where(.swal2-container) div:where(.swal2-popup){
        width: 30em !important;
    }
    /* Force small icon BEFORE animation runs */
.swal2-popup .swal2-icon {
    transform: scale(0.6) !important;
    margin-top: 10px !important;
    margin-bottom: 0 !important;
}

/* Optional: reduce animation impact */
.swal2-show .swal2-icon {
    animation: none !important;
}
.swal2-html-container{
    font-size: 14px !important;
}
div:where(.swal2-container) h2:where(.swal2-title){
    padding: 0 !important;
}
.swal2-popup .swal2-content, .swal2-popup .swal2-html-container{
    margin-top: 0.5rem !important;
    padding-right: 20px !important;
    padding-left: 20px !important;
}
</style>
<script>
    window.showAdminToast = function (message, type = 'success', timeText = 'just now') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '',
                text: message,
                animation: true,
                icon: type === 'error' ? 'error' : type,
                // width: '320px',
                // padding: '1rem',
                // timer: 2200,
                // timerProgressBar: true,
                showConfirmButton: true,
                // showCloseButton: true,
                customClass: {
                    popup: 'my-swal-popup',
                    title: 'my-swal-title',

                    confirmButton: 'btn btn-primary w-20 rounded',
                },
                didOpen: () => {
                    const icon = document.querySelector('.swal2-success');
                    if (icon) {
                        icon.style.transform = 'scale(.5)';
                    }
                },
                // showClass: {
                //     popup: 'animate__animated animate__fadeIn'
                // },
                // hideClass: {
                //     popup: 'animate__animated animate__fadeOutUp'
                // } ,
                buttonsStyling: false,
            });

            return;
        }

        const toastElement = document.getElementById('adminLiveToast');
        const toastBody = document.getElementById('adminToastBody');
        const toastTime = document.getElementById('adminToastTime');

        if (!toastElement || !toastBody || !toastTime || typeof bootstrap === 'undefined') {
            return;
        }

        toastBody.textContent = message;
        toastTime.textContent = timeText;

        toastElement.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning', 'text-bg-info');

        if (type === 'error') {
            toastElement.classList.add('text-bg-danger');
        } else if (type === 'warning') {
            toastElement.classList.add('text-bg-warning');
        } else if (type === 'info') {
            toastElement.classList.add('text-bg-info');
        } else {
            toastElement.classList.add('text-bg-success');
        }

        bootstrap.Toast.getOrCreateInstance(toastElement).show();
    };

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form[data-confirm]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.confirmed === 'true') {
                        form.dataset.confirmed = 'false';
                        return;
                    }

                    event.preventDefault();

                    if (typeof Swal === 'undefined') {
                        form.dataset.confirmed = 'true';
                        form.submit();
                        return;
                    }

                    Swal.fire({
                        title: form.dataset.confirmTitle || 'Are you sure?',
                        text: form.dataset.confirm || 'This action cannot be undone.',
                        icon: form.dataset.confirmIcon || 'warning',
                        showCancelButton: true,
                        confirmButtonText: form.dataset.confirmButton || 'Yes, delete it',
                        cancelButtonText: form.dataset.cancelButton || 'Cancel',
                        customClass: {
                            confirmButton: 'btn btn-primary me-2',
                            cancelButton: 'btn btn-outline-secondary',
                        },
                        buttonsStyling: false,
                        reverseButtons: true,
                    }).then(function (result) {
                        if (!result.isConfirmed) {
                            return;
                        }

                        form.dataset.confirmed = 'true';
                        form.requestSubmit ? form.requestSubmit() : form.submit();
                    });
                });
            });

            @if (session('success'))
                window.showAdminToast(@js(session('success')), 'success');
            @endif

            @if (session('error'))
                window.showAdminToast(@js(session('error')), 'error');
            @endif
        });
</script>
