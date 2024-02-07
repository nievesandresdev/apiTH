<?php

namespace App\Utils\Enums;

/**
 * class InventoryError
 *
 * @package App\Utils\Enums;
 * @author David Rivero <[<davidmriverog@gmail.com>]>
 */
class InventoryError
{
    /**
     * @string
     */
    const INVALID_FIELDS = 'invalid_fields';

    /**
     * @string
     */
    const INTERNAL_SERVER = 'internal_server_error';

    /**
     * @string
     */
    const REQUEST_NOT_PATH = 'request_not_path';

    /**
     * @string 
     */
    const IS_NOT_REGISTERED = 'is_not_registered';

    /**
     * @string 
     */
    const CUSTOM_ERROR_MESSAGE = 'custom_error_message';

    /**
     * @string 
     */
    const IS_ALREADY_REGISTERED = 'is_already_registered';

    /**
     * @string 
     */
    const NO_RECORDS_FOUND = 'no_record_found';

    /**
     * @string
     */
    const INVALID_CREDENTIALS = 'invalid_credentials';

     /**
     * @string
     */
    const INVALID_FILE = 'invalid_file';

    const INVALID_COLLECTION = 'invalid_collection';

    const EMPTY_ROW = 'empty_row';

    const INVALID_USER = 'invalid_user';

    const CODE_REPEAT='code_repeat';

    const NONE_NOTES='none_notes';

    const NONE_CAMPAIGNS='none_campaigns';

    /**
     * Status codes translation table.
     *
     * 
     * 
     * @var array
     */
    public static $list_erros = [
        'invalid_fields' => 'Is required or is failing fields %s please see documentation!',
        'internal_server_error' => 'An internal error occurred while performing this request',
        'request_not_path' => 'Request path does not registered',
        'is_not_registered' => '%s does not registered in our system',
        'is_already_registered' => '%s is already registered in our system',
        'custom_error_message' => '%s',
        'no_record_found' => 'No records found',
        'invalid_credentials' => 'Credenciales invalidas, por favor verifica tus credenciales',
        'invalid_file' => 'El archivo no cumple el estandar, verifique los encabezados y que las colecciones existan',
        'invalid_collection' => 'La coleccion no ha sido registrada o no pertenece a la campaña',
        'empty_row'=>'el codigo del usuario esta vacio en alguna fila',
        'invalid_user'=>'El usuario no existe en la base de datos',
        'code_repeat'=>'El codigo esta repetido',
        'none_notes'=>'No existen notas en la campaña, o no se han almacenado registros',
        'none_campaigns'=>'No existen notas con ese articulo',

    ];

    /**
     * Set Text Error by Code.
     * 
     * @param string $code   
     * @param string $element 
     * @return string
     */
    public static function getErrorStatus($code, $element=null)
    {

        if(!is_null($element)){

            switch ($code) {

                case InventoryError::INVALID_FIELDS :
                    
                    $fields = implode(', ', $element);

                    return [
                        'status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST,
                        'error_code' => $code,
                        'error_message' => sprintf(self::$list_erros[$code], $fields),
                        'error_index' => $element
                    ];

                    break;
                
                default:
                    
                    return [
                        'status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST,
                        'error_code' => $code,
                        'error_message' => sprintf(self::$list_erros[$code], $element)
                    ];

                    break;
            }
        }

        return isset(self::$list_erros[$code])
            ? [
                'status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST,
                'error_code' => $code,
                'error_message' => self::$list_erros[$code]
            ]
            : [
                'status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST,
                'error_code' => $code,
                'error_message' => 'unknown status'
            ];
    }
}