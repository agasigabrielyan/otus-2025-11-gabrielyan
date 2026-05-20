document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('task-form');
    const result = document.getElementById('task-result');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const data = {
            NAME: form.NAME.value,
            DESCRIPTION: form.DESCRIPTION.value,
            USERS: form.USERS.value.split(',').map(u => parseInt(u.trim()))
        };

        BX.ajax.runAction('otus:taskmanager.task.add', { // имя модуля и контроллера
            data: { data }
        }).then(function(response) {
            if(response.data.success) {
                result.innerHTML = 'Задача создана! ID: ' + response.data.taskId;
            } else {
                result.innerHTML = 'Ошибка: ' + (response.data.error || 'неизвестная');
            }
        });
    });
});