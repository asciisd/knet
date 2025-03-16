# KNET Technical Documentation

## Test Cards

| Card Number       | Expiry Date | PIN  | Result        | Description                    |
|------------------|-------------|------|---------------|--------------------------------|
| 8888880000000001 | 09/25       | 1234 | CAPTURED      | Successful payment             |
| 8888880000000002 | 05/25       | 1234 | NOT CAPTURED  | Failed payment                 |
| 8888880000000003 | 09/25       | 1234 | DECLINED      | Card declined                  |
| 8888880000000004 | 05/25       | 1234 | RESTRICTED    | Card restricted               |

## Response Codes

| Result Code    | Description                                      | Action                                    |
|---------------|--------------------------------------------------|-------------------------------------------|
| CAPTURED      | Payment successfully processed                    | Process order                             |
| NOT CAPTURED  | Payment failed to process                        | Retry payment or contact support          |
| DECLINED      | Card declined by issuing bank                    | Try different card                        |
| RESTRICTED    | Card restricted from online transactions         | Contact card issuer                       |
| ABANDONED     | Customer abandoned the payment                   | Allow customer to retry                   |
| CANCELLED     | Customer cancelled the payment                   | Allow customer to retry                   |
| TIMEDOUT      | Session timed out                               | Start new payment session                 |
| UNKNOWN       | Unknown error occurred                          | Contact KNET support                      |

## XML Request Format

### Payment Inquiry
```xml
<?xml version="1.0" encoding="UTF-8"?>
<request>
    <id>YOUR_TRANSPORT_ID</id>
    <password>YOUR_TRANSPORT_PASSWORD</password>
    <action>8</action>
    <amt>10.000</amt>
    <transid>TRACK_ID</transid>
    <udf5>TrackID</udf5>
    <trackid>TRACK_ID</trackid>
</request>
```

### Refund Request
```xml
<?xml version="1.0" encoding="UTF-8"?>
<request>
    <id>YOUR_TRANSPORT_ID</id>
    <password>YOUR_TRANSPORT_PASSWORD</password>
    <action>2</action>
    <amt>10.000</amt>
    <transid>TRACK_ID</transid>
    <udf5>TrackID</udf5>
    <trackid>TRACK_ID</trackid>
</request>
```

## Response Format

### Successful Response
```json
{
    "result": "CAPTURED",
    "auth": "123456",
    "ref": "123456789012",
    "tranid": "123456789012345",
    "postdate": "0123",
    "trackid": "track_id_123",
    "payid": "123456789012345",
    "amt": "10.000",
    "udf1": null,
    "udf2": null,
    "udf3": null,
    "udf4": null,
    "udf5": "TrackID"
}
```

### Error Response
```json
{
    "Error": "Invalid Card",
    "ErrorText": "Card number is not valid",
    "trackid": "track_id_123"
}
```

## Implementation Notes

### Amount Formatting
- Always use 3 decimal places
- Use dot (.) as decimal separator
- No thousands separator
- Example: "10.000" for 10 KWD

### URL Parameters
- Payment URL: `?param=tranInit`
- Response URL: Must be absolute URL
- Error URL: Must be absolute URL and should accept query parameters

### Security
1. Always validate the response signature
2. Store the transaction ID and amount
3. Compare response amount with stored amount
4. Use HTTPS for all endpoints
5. Implement request timeouts
6. Log all transactions

### Best Practices
1. Implement idempotency for payment operations
2. Handle network timeouts gracefully
3. Implement proper error logging
4. Use database transactions where appropriate
5. Validate all input data
6. Implement proper exception handling
7. Use event listeners for payment status changes

## Debugging Tips

1. Enable debug mode in development:
```php
'debug' => env('KNET_DEBUG_MODE', true)
```

2. Check logs for detailed error messages:
```php
Log::channel('knet')->error('Payment failed', [
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
]);
```

3. Monitor transaction status:
```php
$transaction->refresh();
Log::info('Transaction status', [
    'id' => $transaction->id,
    'status' => $transaction->result,
    'paid' => $transaction->paid
]);
```

## Common Issues

1. **Invalid Response Format**
   - Check XML structure
   - Verify content type headers
   - Ensure proper encoding

2. **Payment Timeouts**
   - Implement proper retry logic
   - Set appropriate timeout values
   - Handle network errors

3. **Refund Failures**
   - Verify transaction is refundable
   - Check refund amount
   - Validate transaction status

4. **URL Encoding Issues**
   - Use proper URL encoding for parameters
   - Handle special characters
   - Validate URL format

## Support

For technical support:
- Email: support@asciisd.com
- Documentation: https://docs.asciisd.com/knet
- GitHub Issues: https://github.com/asciisd/knet/issues 