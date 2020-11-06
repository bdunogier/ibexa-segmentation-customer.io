# Ibexa Platform customer.io segmentation

A proof-of-concept of replacing Ibexa Platform 3.2 segmentation storage with customer.io.

The segments from a [customer.io|https://customer.io] account will be listed under the customer.io segments group.
New manual segments can be created. The segments associated to users will be listed in their profile. They can be used
to customize the experience like regular segments.

## Installation

Add the following entry to your `composer.json` repositories key:
```
{ "type": "vcs", "url": "https://github.com/bdunogier/ibexa-segmentation-customer.io" }
```

Run `composer require bdunogier/ibexa-segmentation-customer.io:dev-master` to install the package.

Add the bundle to `bundles.php`:
```
    // ...
    Ibexa\SegmentationCustomerIo\Symfony\IbexaSegmentationCustomerIoBundle::class => ['all' => true],
]
```

Configure your customer.io track and API credentials by defining the following environment variables, for instance
in `.env.local`:

```
CUSTOMER_IO_API_KEY=
CUSTOMER_IO_TRACKING_SITE_ID=
CUSTOMER_IO_TRACKING_API_KEY=
CUSTOMER_IO_TRACKING_CREDENTIALS=${CUSTOMER_IO_TRACKING_SITE_ID}:${CUSTOMER_IO_TRACKING_API_KEY}
```

### Frontend tracking
Users are identified and their behaviour tracked using a javascript tracker.

In the project's main template, add this snippet at the end of your pagelayout, before the HTML closing tag:
```twig
{% if is_granted('IS_AUTHENTICATED_FULLY') %}
    {% include '@ezdesign/customer.io/tracker.html.twig' with {
        'siteId': '%env(CUSTOMER_IO_TRACKING_SITE_ID)',
        'customAttributes': {
            'first_name': app.user.APIUser.getFieldValue('first_name'),
            'last_name': app.user.APIUser.getFieldValue('last_name')

        }
    }%}
{% endif %}
```

`customAttributes` is optional. Any custom attribute, based on user data or some other source, can be set here and added
to the person on customer.io.

## Warnings
Automatic segments can not be deleted nor edited. Doing so will result in an error.

The user profile is not synchronized. The email is used as the customer id, but no other information is sent to customer.io yet. 
