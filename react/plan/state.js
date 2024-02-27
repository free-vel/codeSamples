var state = {
    form: {
        title: '',
        start_date: '',
        start_time: '',
        access_type: '',
        is_active: 0,
        all_listings: 0,
        listing_id: '',
        triggers: [],
        activities: [],
        conditions: [],
        entities: []
    },
    formErrors: {
    },
    icons: {
        create_task: 'fa-tasks',
        wait: 'fa-clock-o',
        send_email: 'fa-envelope',
        send_sms: 'fa-mobile',
        create_tag: 'fa-tag',
        create_buyer_profile: 'fa-user',
        contact_tag: 'fa-tag',
        listing_type: 'fa-tag'
    },
    showListingFieldsFor: [
        'homepass_enquiry_received',
        'new_website_enquiry',
    ],
};

export { state };