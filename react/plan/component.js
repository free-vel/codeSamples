import { CommonComponent } from 'components/common-component';

import { state } from './state';
import { Events }  from './events';
import { Plugins } from './plugins';
import { Requests }  from './requests';
import { TemplateController }  from './template-controller';

class ActionPlansPlan extends CommonComponent
{

    constructor(props) 
    {
        super(props);
        this.state = state;
        this.defaultState = _.cloneDeep(this.state);
        this.requests           = new Requests(this);
        this.events             = new Events(this);
        this.plugins            = new Plugins(this);
        this.templateController = new TemplateController(this);

        utils.REACT.bindClassToMethods(this, ['handleChange', 'goBack']);
    }

    componentDidMount() 
    {
        this.el = {};
        this.el.selector = $('#'+this.props.component_id);
        this.plugins.init();
        this.requests.show(this.props.action_plan_id);
    }

    fixActivityOrder() {
        let sortedKeys = this.el.activities_list.sortable('toArray');
        let form = this.state.form;
        sortedKeys.forEach((key, order) => {
            form.activities[key].order = order;
        });
    }

    goBack() {
        if (document.referrer.includes(location.origin)) {
            location.href = document.referrer;
        } else {
            location.href = '/v2/action-plans';
        }
    }

}

utils.REACT.domrender(ActionPlansPlan, $('[component=ActionPlansPlan]'));
export { ActionPlansPlan };