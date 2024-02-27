class Requests 
{
    
    constructor(component) 
    {
        this.component = component;
        utils.REACT.bindClassToMethods(this, ['show', 'duplicate']);
    }

    show(id)
    {
        if (id) {
            utils.FORM.loader('show');
            const component = this.component;
            $.ajax({
                url: '/v2/action-plans/'+id,
                type: 'GET',
                success(data)
                {
                    const entities = data.entities;
                    entities.map(entities => {
                       return entities.show_beefree_editor = false; //sets the open preview modal state for each activity
                    });
                    data.entities = entities;
                    component.setState({ form: data });
                    utils.FORM.loader('hide');
                },
                error(data)
                {
                    toastr.error('Server error occurred. Please try again');
                    utils.FORM.loader('hide');
                }
            });
        }
    }

    runTestAll(id)
    {
        utils.FORM.loader('show');
        var component = this.component;

        $.ajax({
            url: laroute.route('v2.action-plans.run-test-all', {id: id}),
            type: 'POST',
            success(data)
            {
                $(".test-result").removeClass('text-success text-danger');

                data.activities.forEach(function (activity) {
                    var msg = '';
                    var className = '';
                    if(activity.test_done_at !== null) {
                        msg = 'Test successfully completed';
                        className = 'text-success';
                    } else if(activity.error_at !== null) {
                        msg = 'Test failed: ' + Laravel.ActionPlanActivity.ERROR_DESCRIPTIONS[activity.error_code];
                        className = 'text-danger';
                    }

                    if(msg !== '') {
                        $(".test-result[data-id='" + activity.id + "']").first().addClass(className).text(msg);
                    }
                });
                utils.FORM.loader('hide');
            },
            error(data)
            {
                toastr.error('Server error occurred. Please try again');
                utils.FORM.loader('hide');
            }
        });
    }

    duplicate(id, mode) {
        utils.FORM.loader('show');
        $.ajax({
            url: laroute.route('v2.action-plans.duplicate', {id: id}),
            type: 'POST',
            data: {mode: mode},
            success: function (data) {
                toastr.success('Successfully duplicated. Redirect...');
                window.location.href = laroute.route("v2.action-plans.edit", {id: data.id});
            }.bind(this),
            error: function(data)
            {
                toastr.error('Server error occurred. Please try again');
            }.bind(this)
        });
    }
}

export { Requests };
