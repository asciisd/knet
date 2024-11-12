<?php


namespace Asciisd\Knet;


class KPayResponseStatus
{
    const CAPTURED = 'CAPTURED';
    const ABANDONED = 'ABANDONED';
    const CANCELLED = 'CANCELLED';
    const FAILED = 'FAILED';
    const DECLINED = 'DECLINED';
    const RESTRICTED = 'RESTRICTED';
    const VOID = 'VOID';
    const TIMEDOUT = 'TIMEDOUT';
    const UNKNOWN = 'UNKNOWN';
    const NOT_CAPTURED = 'NOT CAPTURED';
    const INITIATED = 'INITIATED';


    const SUCCESS_RESPONSES = ['CAPTURED'];
    const FAILED_RESPONSES = [
        'ABANDONED', 'CANCELLED', 'FAILED', 'DECLINED', 'RESTRICTED', 'VOID', 'TIMEDOUT', 'UNKNOWN', 'NOT CAPTURED'
    ];
    const NEED_MORE_ACTION = ['INITIATED'];
}
