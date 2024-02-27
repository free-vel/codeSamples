class Events 
{

    constructor(component) 
    {
        this.component = component;
        utils.REACT.bindClassToMethods(this, [
            'reloadTemplate',
            'showBeeFreeTemplate'
        ]);
    }
    
    showBeeFreeTemplate(key) {
        this.component.setState(({form}) => {
            form.activities[key].show_beefree_editor = !form.activities[key].show_beefree_editor;
            return {form};
        });
    }

    fixOrder(listType = 'activities') {
        const component = this.component;
        const sortedKeys = component.el[listType+'_list'].sortable('toArray');

        component.setState(({form}) => {
            sortedKeys.forEach((key, order) => {
                form[listType][key].order = order;
            });

            return {form};
        });
    }
}

export { Events };