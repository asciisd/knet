<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en" style="margin: 0;padding: 0;border: 0;">
<head>
    <title>Your receipt from {{ $vendor }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow, noarchive">
    <style type="text/css">
        img + div {
            display: none !important; /* Hides image downloading in Gmail */
        }

        @media screen and (max-width: 600px) {
            /** Gmail **/
            *[class="Gmail"] {
                display: none !important
            }

            /** Wrapper **/
            .Wrapper {
                max-width: 600px !important;
                min-width: 320px !important;
                width: 100% !important;
                border-radius: 0 !important;
            }

            .Section {
                width: 100% !important;
            }

            .Section--last {
                border-bottom-left-radius: 0 !important;
                border-bottom-right-radius: 0 !important;
            }

            /** Notice **/
            .Notice {
                border-bottom-left-radius: 0 !important;
                border-bottom-right-radius: 0 !important;
            }

            /** Header **/
            .Header .Header-left,
            .Header .Header-right {
                border-top-left-radius: 0 !important;
                border-top-right-radius: 0 !important;
            }

            /** Content **/
            .Content {
                width: auto !important;
            }

            /** Divider **/
            .Divider--kill {
                display: none !important;
                height: 0 !important;
                width: 0 !important;
            }

            /** Spacer **/
            .Spacer--gutter {
                width: 20px !important;
            }

            .Spacer--kill {
                height: 0 !important;
                width: 0 !important;
            }

            .Spacer--emailEnds {
                height: 0 !important;
            }

            /** Target **/
            .Target img {
                display: none !important;
                height: 0 !important;
                margin: 0 !important;
                max-height: 0 !important;
                min-height: 0 !important;
                mso-hide: all !important;
                padding: 0 !important;
                width: 0 !important;
                font-size: 0 !important;
                line-height: 0 !important;
            }

            .Target::before {
                content: '' !important;
                display: block !important;
            }

            /** Header **/
            .Header-area {
                width: 100% !important;
            }

            .Header-left,
            .Header-left::before,
            .Header-right,
            .Header-right::before {
                height: 156px !important;
                width: auto !important;
                background-size: 252px 156px !important;
            }

            .Header-left {
                background-position: bottom right !important;
            }

            .Header-left {
                background-image: url('{{ asset('/vendor/knet/img/invoice/Left.png') }}') !important;
            }

            .Header-right {
                background-image: url('{{asset('/vendor/knet/img/invoice/Right.png')}}') !important;
                background-position: bottom left !important;
            }

            .Header-icon,
            .Header-icon::before {
                width: 96px !important;
                height: 156px !important;
                background-size: 96px 156px !important;
            }

            .Header-icon {
                width: 96px !important;
                height: 156px !important;
                background-image: url('{{ asset('/vendor/knet/img/invoice/twelve_degree_icon.png') }}') !important;
                background-position: bottom center !important;
            }

            /** Table **/
            .Table-content {
                width: auto !important;
            }

            .Table-rows {
                width: 100% !important;
            }
        }

        @media screen and (max-width: 599px) {
            /** Data Blocks **/
            .DataBlocks-item {
                display: block !important;
                width: 100% !important;
            }

            .DataBlocks-spacer {
                display: block !important;
                height: 12px !important;
                width: auto !important;
            }
        }
    </style>
</head>
<body class="Email"
      style="margin: 0;padding: 0;border: 0;background-color: #f1f5f9;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;min-width: 100% !important;width: 100% !important;">
<div class="Preheader"
     style="display: none !important;max-height: 0;max-width: 0;mso-hide: all;overflow: hidden;color: #ffffff;font-size: 1px;line-height: 1px;opacity: 0;visibility: hidden;">

</div>
<div class="Background" style="min-width: 100%;width: 100%;background-color: #f1f5f9;">
    <table class="Wrapper" align="center"
           style="border: 0;border-collapse: collapse;margin: 0 auto !important;padding: 0;max-width: 600px;min-width: 600px;width: 600px;">
        <tbody>
        <tr>
            <td style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;">

                <table class="Divider Divider--small Divider--kill" width="100%"
                       style="border: 0;border-collapse: collapse;margin: 0;padding: 0;">
                    <tbody>
                    <tr>
                        <td class="Spacer Spacer--divider" height="20"
                            style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                            &nbsp;
                        </td>
                    </tr>
                    </tbody>
                </table>

                <div class="Shadow"
                     style="border-bottom-left-radius: 5px;border-bottom-right-radius: 5px;box-shadow: 0 7px 14px 0 rgba(50,50,93,0.10), 0 3px 6px 0 rgba(0,0,0,0.07);">

                    <table dir="ltr" class="Section Header" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Header-left Target"
                                style="background-color: {{ $primary_color }};border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-size: 0;line-height: 0px;mso-line-height-rule: exactly;background-size: 100% 100%;border-top-left-radius: 5px;"
                                align="right" height="156" valign="bottom" width="252">
                                <a href="#" style="pointer-events: none;">
                                    <img alt="" height="156" width="252"
                                         src="{{ asset('/vendor/knet/img/invoice/Left.png') }}"
                                         style="display: block;border: 0;line-height: 100%;width: 100%;">
                                </a>
                            </td>
                            <td class="Header-icon Target"
                                style="background-color: {{ $primary_color }};border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-size: 0;line-height: 0px;mso-line-height-rule: exactly;background-size: 100% 100%;"
                                align="center" height="156" valign="bottom" width="96">
                                <a href="{{ \App\Providers\RouteServiceProvider::HOME }}"
                                   style="-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;outline: 0;text-decoration: none;">
                                    <img alt="" height="156" width="96"
                                         src="{{ asset('/vendor/knet/img/invoice/twelve_degree_icon.png') }}"
                                         style="display: block;border: 0;line-height: 100%;width: 100%;"/>
                                </a>
                            </td>
                            <td class="Header-right Target"
                                style="background-color: {{ $primary_color }};border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-size: 0;line-height: 0px;mso-line-height-rule: exactly;background-size: 100% 100%;border-top-right-radius: 5px;"
                                align="left" height="156" valign="bottom" width="252">
                                <a href="#" style="pointer-events: none;">
                                    <img alt="" height="156" width="252"
                                         src="{{ asset('/vendor/knet/img/invoice/Right.png') }}"
                                         style="display: block;border: 0;line-height: 100%;width: 100%;">
                                </a>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Title" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                            <td class="Content Title-copy Font Font--title" align="center"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;width: 472px;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #32325d;font-size: 24px;line-height: 32px;">
                                Receipt from <span dir="ltr">{{ $vendor }}</span>
                            </td>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Divider" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--divider" height="8"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Title" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                            <td class="Content Title-copy Font Font--title" align="center"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;width: 472px;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #8898aa;font-size: 15px;line-height: 18px;">
                                Payment #{{ $payment->receiptNo() }}
                            </td>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>

                        <tr>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                            <td class="Content Title-copy Font Font--title" align="center"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;width: 472px;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #8898aa;font-size: 15px;line-height: 18px;">
                                Reference #{{ $payment->referenceNo() }}
                            </td>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Divider" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--divider" height="4"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Divider" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--divider" height="24"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section DataBlocks" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;width: 100%;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                            <td class="Content"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;width: 472px;">
                                <table class="DataBlocks DataBlocks--three"
                                       style="border: 0;border-collapse: collapse;margin: 0;padding: 0;width: 100%;">
                                    <tbody>
                                    <tr>
                                        <td class="DataBlocks-item" valign="top"
                                            style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;">
                                            <table style="border: 0;border-collapse: collapse;margin: 0;padding: 0;">
                                                <tbody>
                                                <tr>
                                                    <td class="Font Font--caption Font--uppercase Font--mute Font--noWrap"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #8898aa;font-size: 12px;line-height: 16px;white-space: nowrap;font-weight: bold;text-transform: uppercase;">
                                                        Amount paid
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="Font Font--body Font--noWrap"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #525f7f;font-size: 15px;line-height: 24px;white-space: nowrap;">
                                                        {{ $payment->amount() }}
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>

                                        <td class="Spacer DataBlocks-spacer" width="20"
                                            style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                            &nbsp;
                                        </td>

                                        <td class="DataBlocks-item" valign="top"
                                            style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;">
                                            <table style="border: 0;border-collapse: collapse;margin: 0;padding: 0;">
                                                <tbody>
                                                <tr>
                                                    <td class="Font Font--caption Font--uppercase Font--mute Font--noWrap"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #8898aa;font-size: 12px;line-height: 16px;white-space: nowrap;font-weight: bold;text-transform: uppercase;">
                                                        Payment method
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="Font Font--body Font--noWrap"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #525f7f;font-size: 15px;line-height: 24px;white-space: nowrap;">
                              <span>
                                <img alt="Knet" height="16"
                                     src="{{ $payment->paymentMethodIcon() }}"
                                     style="border: 0; margin: 0; padding: 0; vertical-align: text-bottom;">
                              </span>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>

                                        <td class="Spacer DataBlocks-spacer" width="20"
                                            style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                            &nbsp;
                                        </td>

                                        <td class="DataBlocks-item" valign="top"
                                            style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;">
                                            <table style="border: 0;border-collapse: collapse;margin: 0;padding: 0;">
                                                <tbody>
                                                <tr>
                                                    <td class="Font Font--caption Font--uppercase Font--mute Font--noWrap"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #8898aa;font-size: 12px;line-height: 16px;white-space: nowrap;font-weight: bold;text-transform: uppercase;">
                                                        Result
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="Font Font--body Font--noWrap"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #525f7f;font-size: 15px;line-height: 24px;white-space: nowrap;">
                                    <span>
                                                            <img alt="result" height="16"
                                                                 src="{{ $payment->statusIcon() }}"
                                                                 style="border: 0; margin: 0; padding: 0; vertical-align: text-bottom;"/>
                                                        </span>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Divider" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--divider" height="32"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Copy"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                            <td class="Content Font Font--caption Font--uppercase Font--mute Delink"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;width: 472px;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #8898aa;font-size: 12px;line-height: 16px;font-weight: bold;text-transform: uppercase;">
                                Summary
                            </td>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td class="Spacer Spacer--divider" colspan="3" height="12"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Table" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--kill" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                            <td class="Content"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;width: 472px;">
                                <table class="Table-body"
                                       style="border: 0;border-collapse: collapse;margin: 0;padding: 0;width: 100%;background-color: #f6f9fc;border-radius: 4px;">
                                    <tbody>
                                    <tr>
                                        <td class="Spacer Spacer--divider" colspan="3" height="4"
                                            style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                            &nbsp;
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="Spacer Spacer--gutter" width="20"
                                            style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                            &nbsp;
                                        </td>
                                        <td class="Table-content"
                                            style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;width: 432px;">
                                            <table class="Table-rows" width="432"
                                                   style="border: 0;border-collapse: collapse;margin: 0;padding: 0;">
                                                <tbody>
                                                <tr>
                                                    <td class="Table-divider Spacer" colspan="3" height="6"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="Table-divider Spacer" colspan="3" height="6"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="Table-description Font Font--caption Font--uppercase Font--mute Delink"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #8898aa;font-size: 12px;line-height: 16px;font-weight: bold;text-transform: uppercase;">
                                                        {{ $payment->date()->toFormattedDateString() }}
                                                    </td>
                                                    <td class="Spacer Table-gap" width="8"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                    <td class="Spacer Table-gap"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="Table-divider Spacer" colspan="3" height="6"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="Table-divider Spacer" colspan="3" height="6"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="Table-description Font Font--body"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #525f7f;font-size: 15px;line-height: 24px;">
                                                        <div
                                                            style="-ms-word-break: break-all;word-break: break-all;word-break: break-word;">
                                                            Deposite
                                                        </div>
                                                    </td>
                                                    <td class="Spacer Table-gap" width="8"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                    <td class="Table-amount Font Font--body" align="right" valign="top"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #525f7f;font-size: 15px;line-height: 24px;">
                                                        {{$payment->amount()}}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="Table-divider Spacer" colspan="3" height="6"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="Table-divider Spacer" colspan="3" height="6"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="Spacer" bgcolor="e6ebf1" colspan="3" height="1"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="Table-divider Spacer" colspan="3" height="8"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                </tr>


                                                <tr>
                                                    <td class="Table-divider Spacer" colspan="3" height="6"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="Table-description Font Font--body Font--alt"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #525f7f;font-size: 15px;line-height: 24px;font-weight: bold;">
                                                        Amount paid
                                                    </td>
                                                    <td class="Spacer Table-gap" width="8"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                    <td class="Table-amount Font Font--body Font--alt" align="right"
                                                        valign="top"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #525f7f;font-size: 15px;line-height: 24px;font-weight: bold;">
                                                        {{$payment->amount()}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="Table-divider Spacer" colspan="3" height="6"
                                                        style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                                        &nbsp;
                                                    </td>
                                                </tr>

                                                </tbody>
                                            </table>
                                        </td>
                                        <td class="Spacer Spacer--gutter" width="20"
                                            style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                            &nbsp;
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="Spacer Spacer--divider" colspan="3" height="4"
                                            style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                            &nbsp;
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td class="Spacer Spacer--kill" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Divider Divider--large" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--divider" height="44"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Separator" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                            <td class="Spacer" bgcolor="e6ebf1" height="1"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Divider" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--divider" height="32"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Copy"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                            <td class="Content Footer-help Font Font--body"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;width: 472px;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #525f7f;font-size: 15px;line-height: 24px;">
                                If you have any questions, contact <span dir="ltr">{{ $vendor }}</span> at <a
                                    href="mailto:{{ $email }}"
                                    style="white-space: nowrap;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;outline: 0;text-decoration: none;color: #556cd6;">{{ $email }}</a>
                                or call at <a href="tel:{{ $phone }}"
                                              style="white-space: nowrap;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;outline: 0;text-decoration: none;color: #556cd6;">{{ $phone }}</a>.
                            </td>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Divider" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--divider" height="16"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Divider" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--divider" height="16"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Separator" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                            <td class="Spacer" bgcolor="e6ebf1" height="1"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Divider" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--divider" height="32"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Divider Divider--small" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--divider" height="20"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Copy"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                            <td class="Content Footer-legal Font Font--caption Font--mute"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;width: 472px;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #8898aa;font-size: 12px;line-height: 16px;">
                                Something wrong with the email? <a class="browser-link"
                                                                   href="{{ route('knet.receipt', $payment->id) }}"
                                                                   style="border: 0; margin: 0; padding: 0; color: #556cd6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif; text-decoration: none;"
                                                                   target="_blank" rel="noreferrer"><span
                                        style="border: 0; margin: 0; padding: 0; color: #556cd6; text-decoration: none;">View it in your browser.</span></a>
                            </td>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Copy"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                            <td class="Content Footer-legal Font Font--caption Font--mute"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;width: 472px;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Ubuntu, sans-serif;mso-line-height-rule: exactly;vertical-align: middle;color: #8898aa;font-size: 12px;line-height: 16px;">
                                You're receiving this email because you made a payment at
                                <a href="{{ \App\Providers\RouteServiceProvider::HOME }}"
                                   style="-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;outline: 0;text-decoration: none;color: #556cd6;"><span
                                        dir="ltr">{{ $vendor }}</span></a>.
                            </td>
                            <td class="Spacer Spacer--gutter" width="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="Section Section--last Divider Divider--large" width="100%"
                           style="border: 0;border-collapse: collapse;margin: 0;padding: 0;background-color: #ffffff;border-bottom-left-radius: 5px;border-bottom-right-radius: 5px;">
                        <tbody>
                        <tr>
                            <td class="Spacer Spacer--divider" height="64"
                                style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
                                &nbsp;
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<table class="Divider Divider--small Divider--kill" width="100%"
       style="border: 0;border-collapse: collapse;margin: 0;padding: 0;">
    <tbody>
    <tr>
        <td class="Spacer Spacer--divider" height="20"
            style="border: 0;border-collapse: collapse;margin: 0;padding: 0;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;color: #ffffff;font-size: 1px;line-height: 1px;mso-line-height-rule: exactly;">
            &nbsp;
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
