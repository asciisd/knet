# Upgrade Guide

## Upgrading To 1.* From 2.*

### Minimum Versions

The following required dependency versions have been updated:

- The minimum PHP version is now v7.2
- The minimum Laravel version is now v6.0

### Added `PaymentActionRequired` exception

- was

```php
    $payment = $request->user()->pay($request->amount, [
                'udf1' => $request->user()->name,
                'udf2' => $request->user()->email,
                'udf3' => $trading_account->number
            ]);
```

- now

```php
        try {
            $payment = $request->user()->pay($request->amount, [
                'udf1' => $payable->name,
                'udf2' => $payable->email,
                'udf3' => $trading_account->number
            ]);
        } catch (\Asciisd\Knet\Exceptions\PaymentActionRequired $exception) {
            // here you can get payment from exception and redirect user to the url, or redirect him on finally closure
            // the reason for this exception is that you can differ between the methods that just initiated and the ones they are faield or captured
            $payment = $exception->payment;
        } finally {
            return response()->json([
                'url' => $payment->action_url()
            ]);
        }
```

### Change the `customer()` method to `owner()` inside `Payment` class

- was

```php
    $payment = $request->user()->pay($request->amount, [
                    'udf1' => $payable->name,
                    'udf2' => $payable->email,
                    'udf3' => $trading_account->number
                ]);
    $payment->customer()->id;
```

- now

```php
    $payment = $request->user()->pay($request->amount, [
                    'udf1' => $payable->name,
                    'udf2' => $payable->email,
                    'udf3' => $trading_account->number
                ]);
    $payment->owner()->id;
```

### Removed constants from `Payment` and moved it to the new class called `KPayResponseStatus`

- was

```php
    Payment::CAPTURED;
    Payment::NOT_CAPTURED;
    Payment::PENDING;
    Payment::CURRENCY;
```

- now

```php
    // strings
    KPayResponseStatus::CAPTURED;
    KPayResponseStatus::ABANDONED;
    KPayResponseStatus::CANCELLED;
    KPayResponseStatus::FAILED;
    KPayResponseStatus::DECLINED;
    KPayResponseStatus::RESTRICTED;
    KPayResponseStatus::VOID;
    KPayResponseStatus::TIMEDOUT;
    KPayResponseStatus::UNKNOWN;
    KPayResponseStatus::NOT_CAPTURED;
    KPayResponseStatus::INITIATED;

    // arrays
    KPayResponseStatus::SUCCESS_RESPONSES;
    KPayResponseStatus::FAILED_RESPONSES;
    KPayResponseStatus::NEED_MORE_ACTION;
```

### `Payment` class changes

- from `isCaptured()` to `isSucceeded()`
- from `isFailed()` to `isFailure()`
- from `isPending()` to `requiresAction()`
- from `url()` to `actionUrl()`

### Removed `Knet` facade

if you ever used `Knet::make()`, that's now changed to `KPayManager::make()`