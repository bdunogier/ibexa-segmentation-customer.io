# Ibexa Platform customer.io segmentation

A proof-of-concept of replacing Ibexa Platform 3.2 segmentation storage with customer.io.

## Installation

Add the following entry to your `composer.json` repositories key:
```
{ "type":  "vcs", "url":  "https://github.com/bunogier/ibexa-segmenation-customer.io" }
```

Run `composer require bdunogier/ibexa-segmentation-customer.io` to install the package.

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
CUSTOMER_IO_TRACKING_CREDENTIALS=
```

The tracking credentials must be supplied as `{siteID}:{API Key}`.
