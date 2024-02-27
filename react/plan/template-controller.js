import {BeeFree} from "../../../../components/global/marketing/marketing-templates/beefree/component";
import {ActionPlansTestMessage} from "../modal/test_message_sending/component";

class TemplateController
{
    default(component) 
    {
        const form = component.state.form;
        const disabled = form.parent && !form.parent.is_active;

        return (
            <div id={ component.props.component_id } className="action-plans-plan">
                <div className="row mb-15">
                    <strong className="col-md-3 text-md-right mobile-title">Title:</strong>
                    <div className="col-md-6">
                        <span>{form.title}</span>
                    </div>
                    <div className="col-md-3 d-flex text-right mobile-block">
                        <div>
                            <a href={laroute.route("v2.action-plans.index")}
                               className="btn btn-default waves-effect waves-classic ml-5">
                                Return to List
                            </a>
                        </div>
                        <div className="edit-btn-container">
                            {form.created_by == component.props.user_id ?
                                <a href={laroute.route("v2.action-plans.edit", {id: component.props.action_plan_id})}>
                                    <i className="fa fa-pencil-square-o edit-btn trigger-edit"
                                       data-toggle="tooltip" data-placement="top" data-trigger="hover"
                                       data-original-title="Edit"></i>
                                </a>
                                :
                                <a href='#' onClick={ () => {component.requests.duplicate(form.id, 0)}}>
                                    <i className="fa fa-copy edit-btn"
                                       data-toggle="tooltip" data-placement="top" data-trigger="hover"
                                       data-original-title="Duplicate"></i>
                                </a>
                            }
                        </div>
                    </div>
                </div>
                <div className="row mb-15">
                    <strong className="col-md-3 text-md-right">Description:</strong>
                    <div className="col-md-6">
                        <span>{form.description}</span>
                    </div>
                </div>
                <div className="row mb-15">
                    <strong className="col-md-3 text-md-right">Access Type:</strong>
                    <div className="col-md-2">
                        <span>{Laravel.ActionPlan.ACCESS_TYPES[form.access_type]}</span>
                    </div>
                    <div className="col-md-1  text-md-right">
                        <strong>Created:</strong>
                    </div>
                    {form.created_at ?
                        <div className="col-md-2">
                            <span>{moment(form.created_at, ['YYYY-MM-DD HH:mm:ss']).format('YYYY-MM-DD h:mma')}</span>
                        </div> : false
                    }
                </div>
                {form.trigger !== 'manual' ?
                    <div>
                        <div className="row mb-15">
                            <strong className="col-md-3 text-md-right">Start Date:</strong>
                            <div className="col-md-2 start-date-wrap">
                                <span>{form.start_date}</span>
                                <i className="fa fa-calendar"></i>
                            </div>
                            <div className="col-md-1 text-md-right">
                                <strong>Status: </strong>
                            </div>
                            <div className="col-md-2">
                                {disabled ?
                                    <span className="inactive-plan">Disabled by parent Plan</span> :
                                    form.is_active ?
                                        <span className="active-plan">Active</span> :
                                        <span className="inactive-plan">Inactive</span>
                                }
                            </div>
                        </div>
                        <div className="row mb-15">
                            <strong className="col-md-3 text-md-right">Start Time:</strong>
                            <div className="col-md-2 start-time-wrap">
                                <span>{form.start_time}</span>
                                <i className="fa fa-clock-o"></i>
                            </div>
                        </div>
                    </div> : false
                }
                {
                    form.additional_data
                        ? this.additionalDataTemplate(form.trigger, form.additional_data)
                        : false
                }
                {form.contact ?
                    <div className="row mt-15">
                        <strong className="col-md-3 text-md-right">Contact:</strong>
                        <div className="col-md-4">
                            <a href={laroute.route('v2.contacts.show', {id: form.contact.id})}>{form.contact.full_name}</a>
                        </div>
                    </div> : false
                }
                {form.all_listings ?
                    <div className="row mt-15">
                        <strong className="col-md-3 text-md-right">Listing:</strong>
                        <div className="col-md-4">For All Listings</div>
                    </div> :
                    form.listing ?
                        <div className="row mt-15">
                            <strong className="col-md-3 text-md-right">Listing:</strong>
                            <div className="col-md-4">
                                <a href={laroute.route('v2.properties.listings.show', {
                                    propertyId: form.listing.property_id,
                                    id: form.listing.id
                                })}>{form.listing.property.address}</a>
                            </div>
                        </div> : false
                }
                <hr/>
                <div className="row">
                    <div className="col-md-3">
                        <p className="trigger-title-wrap justify-content-start justify-content-md-end">
                            <strong>Trigger: </strong>
                            <i className="fa fa-check-square-o"></i>
                        </p>
                    </div>
                    <div className="col-md-6">
                        <div className="trigger">
                            <b>{component.state.form.trigger_name}</b>
                            <hr/>
                            <p>{Laravel.ActionPlan.TRIGGER_DESCRIPTIONS[component.state.form.trigger]}</p>
                        </div>
                    </div>
                </div>
                <hr/>
                {this.entitiesList(component)}
                <hr/>
                <div className="row">
                    <div className="col-md-6 offset-md-3 text-md-right">
                        <button type="button" className="btn btn-default waves-effect waves-classic ml-5 mb-10"
                                onClick={component.goBack}>Back</button>
                    </div>
                </div>
                <ActionPlansTestMessage
                    component_id={'modal' + component.props.component_id}
                    trigger=".trigger-action-plans-test-message-modal"
                    actionPlan = {component.state.form}
                />
            </div>
        );
    }

    create_task(component, activity, key)
    {
        return (
            <div>
                <p>Create a task on the Agent's task board.</p>
                <p><span className="title">Task Title:</span> {activity.data.title}</p>
                <p><span className="title">Task Type:</span> {activity.data.type}</p>
                <p><span className="title">Due Date:</span> +{activity.data.offset_amount} {activity.data.offset_amount === "1" ?
                    activity.data.offset_units.slice(0, -1) :
                    activity.data.offset_units}</p>
                <p><span className="title">Description:</span> {activity.data.description}</p>
                <p><span className="title">Completed:</span> {activity.data.done ? 'Yes' : 'No'}</p>
                <p><span className="title">Assigned To :</span>
                    {activity.data.assigned_to ? activity.data.assigned_to.map(({full_name, id}) =>
                        <span><a className="ml-3" href={laroute.route('v2.agent-profiles.show', {id, by: 'user'})}>
                            {full_name}
                        </a>;</span>
                    ) : ''}
                </p>
            </div>
        )
    }

    create_tag(component, activity, key)
    {
        return (
            <div>
                <p><span className="title">Tag Name:</span> {activity.data.title}</p>
            </div>
        )
    }

    create_buyer_profile(component, activity, key)
    {
        return (
            <div>
                <p>Buyer Profile will be created based on listing information, attached to the plan</p>
            </div>
        )
    }

    send_email(component, activity, key)
    {
        let from = activity.data.from ? activity.data.from.full_name : '';
        return (

            <div>
                <p><span className="title">Subject:</span> {activity.data.subject}</p>
                <p><span className="title">From:</span> {from}</p>
                {activity.data.message.includes('class="attachment-block"')
                    ? <p><span className="title">Content:</span> <div dangerouslySetInnerHTML={{__html: activity.data.message}}/></p>
                    : <p><span className="title">Content:</span> {utils.STRING.strip_tags(activity.data.message)}</p>
                }
                <p><span className="title">Include Email Signature:</span> {activity.data.include_signature ? 'Yes' : 'No'}</p>
                <p><span className="title">Time: </span>
                    {
                    activity.data.send_time
                        ? activity.data.send_time
                        : ((activity.data.send_time_from && activity.data.send_time_to)
                            ? activity.data.send_time_from + ' - ' + activity.data.send_time_to
                            :'Instantly'
                        )
                    }
                </p>
                <p><span className="title">If fails:</span> {utils.STRING.ucfirst(activity.data.if_fails)}</p>
            </div>
        )
    }

    send_an_html_email(component, activity, key) {
        const activityData = activity.data;
        const {
            showBeeFreeTemplate,
            reloadTemplate
        } = component.events;

        return (
            <div>
                <p><span className="title">Recipient(s):</span></p>
                {
                    activityData.to.others && activityData.to.others.length
                        ?
                            activityData.to.others.map(recipient =>
                                <p><a href={laroute.route('v2.agent-profiles.show', {id: recipient.id, by: 'user'})}>{ recipient.full_name }</a></p>
                            )
                        :
                            ''
                }
                <p><span className="title">Send From:</span></p>
                <p><span>{ activityData.sender }</span></p>
                <p><span className="title">Subject:</span></p>
                <p><span>{ activityData.subject }</span></p>
                {
                    activityData.template && Object.keys(activityData.template).length
                        ?
                            <div className="mt-10" id={`action-plan-activity-item-${key}`}>
                                <p><span className="title">Template: </span>{ activityData.template.name }</p>
                                <p>
                                    <button type="button"
                                           className="btn btn-warning"
                                           title="Preview"
                                           onClick={() => showBeeFreeTemplate(key)}
                                    >
                                        Preview
                                    </button>
                                </p>
                                {
                                    activity.show_beefree_editor
                                        ?
                                            <p>
                                                <BeeFree component_id={`action-plans-show-${key}`}
                                                         template={activityData.template}
                                                         reloadTemplate={reloadTemplate}
                                                />
                                            </p>
                                        :
                                        ''
                                }

                            </div>
                        :
                            ''
                }
            </div>
        )
    }

    send_sms(component, activity, key)
    {
        return (
            <div>
                <p><span className="title">Content:</span> {utils.STRING.strip_tags(activity.data.message)}</p>
                <p><span className="title">Include SMS Signature:</span> {activity.data.include_signature ? 'Yes' : 'No'}</p>
                <p><span className="title">Time: </span>
                    {
                    activity.data.send_time
                        ? activity.data.send_time
                        : ((activity.data.send_time_from && activity.data.send_time_to)
                            ? activity.data.send_time_from + ' - ' + activity.data.send_time_to
                            :'Instantly'
                        )
                    }
                </p>
                <p><span className="title">If fails:</span> {utils.STRING.ucfirst(activity.data.if_fails)}</p>
            </div> 
        )
    }

    add_to_contact_group(component, activity, key)
    {
        return (
            <div>
                <p><span className="title">{ activity.data.title }</span></p>
                <p><span className="title">{ activity.data.contact_group }</span></p>
            </div>
        )
    }

    wait(component, activity, key)
    {
        return (
            <div>
                <p>The action plan will pause for {activity.data.amount} {activity.data.amount === "1" ?
                        activity.data.units.slice(0, -1) :
                        activity.data.units} before performing the next activity</p>
            </div>
        )
    }

    additionalDataTemplate(triggerType, data)
    {
        switch (triggerType) {
            case "tag_added_to_contact":
                return <div className="row mt-15">
                    <strong className="col-md-3 text-md-right">Tags:</strong>
                    <div className="col-md-4">
                        {
                            !data.hasOwnProperty('tags') || !data.tags.length
                                ? '-'
                                : data.tags.map((tag) =>
                                    <span className="badge badge-lg badge-success mr-5 mb-5">
                                        { tag.tag }
                                    </span>
                                )
                        }
                    </div>
                </div>;
            default:
                return "";
        }
    }

    contact_tag(component, condition, key)
    {
        return (
            <div>
                <p><span className="title">Tag Name:</span> {condition.data.title}</p>
                <p><span className="title">{condition.data.has_tag === 1 ? 'Has Tag' : 'Does not have Tag'}</span></p>
            </div>
        )
    }

    /**
     * @param component
     * @param condition
     * @param key
     * @returns {JSX.Element}
     */
    listing_type(component, condition, key)
    {
        return (
            <div>
                <p><span className="title">{ condition.data ? (condition.data.is_type == 1 ? 'Is' : 'Is not') : '' } type:</span> { condition.data ? condition.data.type : '' }</p>
            </div>
        )
    }

    entitiesList( component )
    {
        const {form} = component.state;
        let activities = form.entities.filter(entity => entity.entityType === 'activity');
        return (
            <div>
                {activities.length === 0 ? <p className="text-danger text-center">Activities not found</p> : ''}
                {
                    form.entities.map((entity, key) =>
                        this[entity.entityType](component, key, entity)
                    )
                }

            </div>
        );
    }

    condition(component, key, condition)
    {
        return (
            <div className="row" key={ key }>
                <div className="col-md-3">
                    <p className="activity-title-wrap justify-content-start justify-content-md-end">
                        <span className="activity-step">{key + 1}</span>
                        <strong>Condition: </strong>
                        <i className={'fa ' + component.state.icons[condition.type]}></i>
                    </p>
                </div>
                <div className="col-md-6">
                    <div className={'activity mb-20 ' + condition.type.replace('_', '-')}>
                        <div className="title-container">
                            <b>{Laravel.ActionPlanCondition.CONDITION_TYPES[condition.type]['title']}</b>
                            <span dangerouslySetInnerHTML={{__html: component.helpers('form').actionPlanActivityStatusIcon(condition)}}></span>
                        </div>
                        <hr/>
                        { this[condition.type](component, condition, key)}
                        <div style={{ clear: 'both' }}></div>
                    </div>
                </div>
                <div className="col-md-3">
                    <span className="test-result" data-id={condition.id}></span>
                </div>
            </div>
        );
    }

    activity(component, key, activity)
    {
        return (
            <div className="row" key={ key }>
                <div className="col-md-3">
                    <p className="activity-title-wrap justify-content-start justify-content-md-end">
                        <span className="activity-step">{key + 1}</span>
                        <strong>Activity: </strong>
                        <i className={'fa ' + component.state.icons[activity.type]}></i>
                    </p>
                </div>
                <div className="col-md-6">
                    <div className={'activity mb-20 ' + activity.type.replace('_', '-')}>
                        <div className="title-container">
                            <b>{Laravel.ActionPlanActivity.ACTIVITY_TYPES[activity.type]['title']}</b>
                            <span dangerouslySetInnerHTML={{__html: component.helpers('form').actionPlanActivityStatusIcon(activity)}}></span>
                        </div>
                        <hr/>
                        { this[activity.type](component, activity, key)}
                        { activity.type === 'send_email' ?
                            <button
                                type="button"
                                className={"btn btn-success float-right trigger-action-plans-test-message-modal"}
                                data-toggle="tooltip"
                                data-placement="top"
                                data-trigger="hover"
                                data-original-title="Run Test"
                                data-key={key}
                                data-activity-type={activity.type}
                            >
                                Test
                            </button>
                            : false
                        }
                        <div style={{ clear: 'both' }}></div>
                    </div>
                </div>
                <div className="col-md-3">
                    <span className="test-result" data-key={key}></span>
                </div>
            </div>
        );
    }
}

export { TemplateController };
