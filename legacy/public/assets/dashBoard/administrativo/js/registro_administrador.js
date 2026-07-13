/**
 * administrativo-wizard.js
 * Controla la navegación entre pasos del formulario de registro
 * y valida campos requeridos antes de avanzar. Sin dependencias externas,
 * siguiendo el mismo enfoque vanilla JS usado en sidebar.js.
 */

(function () {
  'use strict';

  const form = document.getElementById('formAdministrativo');
  if (!form) return;

  const secciones = form.querySelectorAll('[data-seccion]');
  const pasos = document.querySelectorAll('.stepper__paso');

  function mostrarSeccion(numero) {
    secciones.forEach((seccion) => {
      seccion.style.display = seccion.dataset.seccion === String(numero) ? '' : 'none';
    });

    pasos.forEach((paso) => {
      const numeroPaso = parseInt(paso.dataset.paso, 10);
      paso.classList.remove('stepper__paso--activo', 'stepper__paso--completado');

      if (numeroPaso === numero) {
        paso.classList.add('stepper__paso--activo');
      } else if (numeroPaso < numero) {
        paso.classList.add('stepper__paso--completado');
      }
    });

    // Llevar el scroll al inicio del formulario al cambiar de paso
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function validarSeccion(seccionEl) {
    const campos = seccionEl.querySelectorAll('[required]');
    let esValido = true;

    campos.forEach((campo) => {
      campo.classList.remove('form-field__input--error');

      if (!campo.value.trim()) {
        campo.classList.add('form-field__input--error');
        esValido = false;
      }

      // Validación específica de email en el paso 3
      if (campo.type === 'email' && campo.value.trim()) {
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regexEmail.test(campo.value.trim())) {
          campo.classList.add('form-field__input--error');
          esValido = false;
        }
      }

      // Validación de cédula: solo dígitos, 6-10 caracteres
      if (campo.id === 'cedula' && campo.value.trim()) {
        const regexCedula = /^\d{6,10}$/;
        if (!regexCedula.test(campo.value.trim())) {
          campo.classList.add('form-field__input--error');
          esValido = false;
        }
      }

      // Validación de teléfono: solo dígitos, 7-10 caracteres
      if (campo.id === 'telefono' && campo.value.trim()) {
        const regexTelefono = /^\d{7,10}$/;
        if (!regexTelefono.test(campo.value.trim())) {
          campo.classList.add('form-field__input--error');
          esValido = false;
        }
      }
    });

    return esValido;
  }

  // Botones "Siguiente"
  document.querySelectorAll('.btn-siguiente').forEach((boton) => {
    boton.addEventListener('click', () => {
      const seccionActual = boton.closest('[data-seccion]');

      if (!validarSeccion(seccionActual)) {
        return; // No avanza si hay campos inválidos
      }

      const destino = parseInt(boton.dataset.siguiente, 10);
      mostrarSeccion(destino);
    });
  });

  // Botones "Anterior"
  document.querySelectorAll('.btn-anterior').forEach((boton) => {
    boton.addEventListener('click', () => {
      const destino = parseInt(boton.dataset.anterior, 10);
      mostrarSeccion(destino);
    });
  });

  // Validación final antes de enviar el formulario completo
  form.addEventListener('submit', (evento) => {
    const ultimaSeccion = form.querySelector('[data-seccion="3"]');
    if (!validarSeccion(ultimaSeccion)) {
      evento.preventDefault();
    }
  });

  // Restricción de solo-números en cédula y teléfono mientras se escribe
  ['cedula', 'telefono'].forEach((id) => {
    const input = document.getElementById(id);
    if (input) {
      input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '');
      });
    }
  });
})();