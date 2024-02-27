class Plugins 
{

    constructor(component) 
    {
        this.component = component;
        utils.REACT.bindClassToMethods(this, []);
    }

    init()
    {
        // this.initSortable();
    }

    initSortable()
    {

        let component = this.component;
        component.el.activities_list.sortable({
            placeholder: "ui-sortable-placeholder col-md-6 offset-md-3",
            handle: ".fa-arrows",
            cursor: "move",
            stop: function( event, ui ) {
                component.events.fixOrder('activities');
            }
        });
        component.el.conditions_list.sortable({
            placeholder: "ui-sortable-placeholder col-md-6 offset-md-3",
            handle: ".fa-arrows",
            cursor: "move",
            stop: function( event, ui ) {
                component.events.fixOrder('conditions');
            }
        });
    }

}

export { Plugins };