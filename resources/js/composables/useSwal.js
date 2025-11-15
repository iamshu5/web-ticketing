import Swal from 'sweetalert2'

export function useSwal() {
    const toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer
            toast.onmouseleave = Swal.resumeTimer
        }
    })

    const success = (message) => {
        toast.fire({
            icon: 'success',
            title: message
        })
    }

    const error = (message) => {
        toast.fire({
            icon: 'error',
            title: message
        })
    }

    const warning = (message) => {
        toast.fire({
            icon: 'warning',
            title: message
        })
    }

    const confirm = (options) => {
        return Swal.fire({
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal',
            ...options
        })
    }

    return {
        success,
        error,
        warning,
        confirm
    }
}