const root = document.getElementById('glossaryRoot');

if (root) {
    let templates = {};

    try {
        templates = JSON.parse(root.dataset.templates || '{}');
    } catch (error) {
        templates = {};
    }

    const form = document.getElementById('glosarioForm');
    const formTitle = document.getElementById('formTitle');
    const submitBtn = document.getElementById('submitBtn');
    const formMethod = document.getElementById('formMethod');
    const tema = document.getElementById('tema');
    const titulo = document.getElementById('titulo');
    const terminos = document.getElementById('terminos');
    const idioma = document.getElementById('idioma');
    const storeUrl = root.dataset.storeUrl || '/glosarios/guardar';
    const updateUrlTemplate = root.dataset.updateUrlTemplate || '/glosarios/__ID__';

    const fillTemplate = (templateKey) => {
        const template = templates[templateKey] || templates.personalizado || {};

        if (titulo) titulo.value = template.titulo || '';
        if (terminos) terminos.value = template.terminos || '';
        if (idioma) idioma.value = template.idioma || 'es';
        if (tema) tema.value = templateKey;
    };

    const resetForm = (templateKey = 'medicina') => {
        if (formTitle) formTitle.innerText = 'Configurar glosario';
        if (submitBtn) submitBtn.innerText = 'Guardar glosario';
        if (form) form.action = storeUrl;
        if (formMethod) formMethod.value = 'POST';

        if (form) form.reset();
        fillTemplate(templateKey);
    };

    const editGlosario = (button) => {
        if (!button || !form || !formTitle || !submitBtn || !formMethod || !titulo || !terminos || !idioma || !tema) {
            return;
        }

        formTitle.innerText = 'Editar glosario';
        submitBtn.innerText = 'Actualizar glosario';
        form.action = updateUrlTemplate.replace('__ID__', button.dataset.id || '');
        formMethod.value = 'PUT';
        titulo.value = button.dataset.titulo || '';
        terminos.value = button.dataset.terminos || '';
        idioma.value = button.dataset.idioma || 'es';
        tema.value = 'personalizado';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    root.querySelectorAll('[data-template-trigger]').forEach((button) => {
        button.addEventListener('click', () => {
            const templateKey = button.dataset.templateTrigger || 'medicina';
            resetForm(templateKey);
        });
    });

    root.querySelectorAll('[data-edit-glosary]').forEach((button) => {
        button.addEventListener('click', () => editGlosario(button));
    });

    if (tema) {
        tema.addEventListener('change', () => {
            if (tema.value !== 'personalizado') {
                fillTemplate(tema.value);
            }
        });
    }

    resetForm('medicina');
}
