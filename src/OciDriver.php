<?php

/*
 * This file is part of the Develpup OCI package.
 *
 * (c) Jason Hofer <jason.hofer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Develpup\Oci;

/**
 * Class OciDriver
 *
 * @package Develpup\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-04-14 10:30 AM
 */
class OciDriver
{
    /**#@+
     * Oracle service handler type. Used as the value of "SERVER=" in the connect descriptor.
     *
     * @var string
     */
    const SERVER_DEDICATED = 'dedicated';
    const SERVER_POOLED    = 'pooled';
    const SERVER_SHARED    = 'shared';
    /**#@-*/

    /**
     * Default connection parameters.
     *
     * @link http://doctrine-dbal.readthedocs.org/en/latest/reference/configuration.html#pdo-oci-oci8
     *
     * @var array
     */
    private static $defaultParams = array(
        // Doctrine common parameters
        'host'         => '',
        'port'         => 1521,
        'dbname'       => '',
        'url'          => '',
        // Doctrine Oracle driver parameters
        'servicename'  => '',
        'service'      => false, // If true, uses '(SERVICE_NAME = ...)', otherwise uses '(SID = ...)'.
        'pooled'       => false,
        'instancename' => '',
        'charset'      => null,
        'sessionmode'  => OCI_DEFAULT,
        'persistent'   => false,
        // Develpup OCI parameters
        'sid'          => '',
        'descriptor'   => '',
        'server'       => '', // If specified, must be one of "dedicated", "shared", or "pooled".
    );

    /**
     * Attempts to create a connection with the database.
     *
     * @param array       $params   All connection parameters passed by the user.
     * @param string|null $username The username to use when connecting.
     * @param string|null $password The password to use when connecting.
     *
     * @return OciConnection The database connection.
     */
    public function connect(array $params, $username = null, $password = null)
    {
        $params = array_merge(self::$defaultParams, array_change_key_case($params, CASE_LOWER));

        return new OciConnection(
            $username,
            $password,
            $this->getConnectionString($params),
            $params['charset'],
            $params['sessionmode'],
            $params['persistent']
        );
    }

    /**
     * Gets the name of the driver.
     *
     * @return string
     */
    public function getName()
    {
        return 'develpup_oci';
    }

    /**
     * Returns an appropriate connect descriptor for the given parameters.
     *
     * @link http://www.oracle.com/technetwork/database/enterprise-edition/oraclenetservices-neteasyconnect-133058.pdf
     *
     * @param array $params The connection parameters to return the connect descriptor for.
     *
     * @return string
     *
     * @throws OciException
     */
    protected function getConnectionString(array $params)
    {
        if ($params['descriptor']) {
            return $params['descriptor'];
        } elseif ($params['url']) {
            return $params['url'];
        } elseif (!$params['host']) {
            return $params['dbname'];
        }

        // (SID = ...) or (SERVICE_NAME = ...)
        if ($params['sid']) {
            $service = 'SID=' . $params['sid'];
        } else {
            $serviceName = ($params['servicename'] ?: $params['dbname']);
            $service     = ($params['service'] ? 'SERVICE_NAME=' : 'SID=') . $serviceName;
        }

        // (INSTANCE_NAME = ...)
        if (($instance = $params['instancename'])) {
            $instance = '(INSTANCE_NAME=' . $instance . ')';
        }

        // (SERVER = [dedicated|shared|pooled])
        if (($server = $params['server'])) {
            $server = strtolower($server);
            if (!in_array($server, array(self::SERVER_POOLED, self::SERVER_DEDICATED, self::SERVER_SHARED))) {
                throw new OciException(
                    sprintf(
                        'Expected "server" option to be one of "dedicated", "shared", or "pooled", but got "%s".',
                        $server
                    )
                );
            }
            $server = '(SERVER=' . $server . ')';
        } elseif ($params['pooled']) {
            $server = '(SERVER=pooled)';
        }

        return
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=' . $params['host'] . ')(PORT=' . $params['port'] . '))' .
            '(CONNECT_DATA=(' . $service . ')' . $instance . $server . '))';
    }
}
