<?php

namespace Asciisd\Knet\Contracts;

/**
 * Aggregate interface that combines all payment capabilities.
 *
 * Prefer depending on the narrower interfaces (CreatesPayments, InquiresPayments,
 * RefundsPayments) when only a subset of functionality is needed.
 */
interface PaymentServiceInterface extends CreatesPayments, InquiresPayments, RefundsPayments {}
