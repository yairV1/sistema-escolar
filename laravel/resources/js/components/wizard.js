/**
 * Navegación genérica de wizard por pasos, reutilizable por cualquier
 * formulario multi-paso (Registro de Estudiantes, Docentes, ...).
 *
 * Contrato HTML esperado dentro de `formEl`:
 *   <div class="wizard-step" data-step="1">...</div>
 *   <div class="wizard-step" data-step="2">...</div>
 *   <div class="wizard-pill" data-step-link="1">...</div> (indicador, opcional)
 *   <button data-wizard-next>Siguiente</button>
 *   <button data-wizard-prev>Anterior</button>
 *   <button type="submit">...</button> (solo visible en el último paso)
 */
export function initWizard(formEl, { onValidateStep } = {}) {
    const steps = Array.from(formEl.querySelectorAll('.wizard-step'));
    const pills = Array.from(formEl.querySelectorAll('[data-step-link]'));
    let current = 1;

    function show(step) {
        steps.forEach((el) => el.classList.toggle('d-none', Number(el.dataset.step) !== step));
        pills.forEach((el) => {
            const n = Number(el.dataset.stepLink);
            el.classList.toggle('active', n === step);
            el.classList.toggle('completed', n < step);
        });

        formEl.querySelectorAll('[data-wizard-only-last]').forEach((el) => {
            el.classList.toggle('d-none', step !== steps.length);
        });
        formEl.querySelectorAll('[data-wizard-hide-last]').forEach((el) => {
            el.classList.toggle('d-none', step === steps.length);
        });
        formEl.querySelectorAll('[data-wizard-prev]').forEach((el) => {
            el.classList.toggle('d-none', step === 1);
        });

        current = step;
    }

    formEl.querySelectorAll('[data-wizard-next]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (onValidateStep && !onValidateStep(current)) return;
            if (current < steps.length) show(current + 1);
        });
    });

    formEl.querySelectorAll('[data-wizard-prev]').forEach((btn) => {
        btn.addEventListener('click', () => show(Math.max(1, current - 1)));
    });

    pills.forEach((pill) => {
        pill.addEventListener('click', () => {
            const target = Number(pill.dataset.stepLink);
            if (target < current) show(target);
        });
    });

    show(1);

    return {
        goToStep: show,
        currentStep: () => current,
    };
}
