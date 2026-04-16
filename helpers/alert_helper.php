<?php

/**  * Función para imprimir SweetAlert dinámico con estilo */
function mostrarSweetAlert($tipo, $titulo, $mensaje, $redirect = null)
{
    echo "
    <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap&#39;);

                body {
                    margin: 0;
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: linear-gradient(135deg, #2D4059, #007832);
                    font-family: 'Lato', sans-serif;
                    color: #fff;
                }

                .swal2-popup {
                    font-family: 'Lato', sans-serif !important;
                }

                .swal2-title {
                    color: #2D4059 !important;
                    font-weight: 600 !important;
                }

                .swal2-styled.swal2-confirm {
                    background-color: #007832 !important;
                    border: none !important;
                }

                .swal2-styled.swal2-confirm:hover {
                    background-color: #005d28 !important;
                }

                .swal2-styled.swal2-cancel {
                    background-color: #00304D !important;
                }
            </style>

            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

        </head>
        
        <body>
            <script>
                Swal.fire({
                    icon: '$tipo',
                    title: '$titulo',
                    text: '$mensaje',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#007832',
                    background: '#fff',
                    color: '#2D4059'
                }).then((result) => {
                    " . ($redirect ? "window.location.href = '$redirect';" : "window.history.back();") . "
                });
            </script>
        </body>
    </html>";
}
