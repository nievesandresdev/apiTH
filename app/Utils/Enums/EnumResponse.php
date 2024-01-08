<?php

namespace App\Utils\Enums;

/**
 * Class EnumResponse
 *
 * @package App\Utils\Enums
 * @author  David Rivero <davidmriverog@gmail.com>
 */
    class EnumResponse
{
    // Status 200
    const SUCCESS = 'success';

    // Status 200
    const SUCCESS_OK = 'success_ok';

    // Status 201
    const CREATE_SUCCESS = 'create_success';

    // Status 202
    const ACCEPTED = 'accepted';

    // Status 203
    const NO_CONTENT = 'no_content';

    // Status 400
    const BAD_REQUEST = 'bad_request';

    // Status 400
    const DUPLICATE_ENTRY = 'Duplicate Entry';

    // Status 401
    const UNAUTHORIZED = 'unauthorized';

    // Status 403
    const FORBIDDEN = 'forbidden';

    // Status 404
    const NOT_FOUND = 'not_found';

    // Status 422
    const UNPROCESSABLE_ENTITY = 'Unprocessable_entity';

    // Status 500
    const INTERNAL_SERVER_ERROR = 'internal_server_error';

    // Status 501
    const NOT_IMPLEMENTED = 'not_implemented';

    // Status 503
    const SERVICE_UNAVAILABLE = 'service_unavailable';    

    const ERROR = 'error';

}