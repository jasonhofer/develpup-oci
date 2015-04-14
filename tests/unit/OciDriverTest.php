<?php

/*
 * This file is part of the Develpup OCI package.
 *
 * (c) Jason Hofer <jason.hofer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Develpup\Test\Oci;

use Develpup\Oci\OciDriver;
use Develpup\Oci\OciException;

/**
 * Class OciDriverTest
 *
 * @package Develpup\Test\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-04-14 12:30 PM
 */
class OciDriverTest extends AbstractUnitTestCase
{
    /**
     * @return OciDriver
     */
    protected function newDriver()
    {
        return new OciDriver();
    }

    /**
     * @group driver
     */
    public function testGetConnectionString()
    {
        $tests = array(
            '' =>
                array(),
            'url_str' =>
                array(
                    'url'  => 'url_str',
                    'host' => 'host-name',
                ),
            'descriptor_str' =>
                array(
                    'descriptor' => 'descriptor_str',
                    'host'       => 'host-name',
                ),
            'db_name' =>
                array(
                    'dbname' => 'db_name',
                ),
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=host-name)(PORT=1521))(CONNECT_DATA=(SID=)))' =>
                array(
                    'host' => 'host-name',
                ),
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=host-name)(PORT=2115))(CONNECT_DATA=(SID=)))' =>
                array(
                    'host' => 'host-name',
                    'port' => 2115,
                ),
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=host-name)(PORT=1521))(CONNECT_DATA=(SERVICE_NAME=)))' =>
                array(
                    'host'    => 'host-name',
                    'service' => true,
                ),
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=host-name)(PORT=1521))(CONNECT_DATA=(SERVICE_NAME=db_name)))' =>
                array(
                    'host'    => 'host-name',
                    'service' => true,
                    'dbname'  => 'db_name',
                ),
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=host-name)(PORT=1521))(CONNECT_DATA=(SERVICE_NAME=svc_name)))' =>
                array(
                    'host'        => 'host-name',
                    'service'     => true,
                    'servicename' => 'svc_name',
                    'dbname'      => 'db_name',
                ),
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=host-name)(PORT=1521))(CONNECT_DATA=(SID=svc_name)))' =>
                array(
                    'host'        => 'host-name',
                    'servicename' => 'svc_name',
                    'dbname'      => 'db_name',
                ),
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=host-name)(PORT=1521))(CONNECT_DATA=(SID=db_name)))' =>
                array(
                    'host'   => 'host-name',
                    'dbname' => 'db_name',
                ),
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=host-name)(PORT=1521))(CONNECT_DATA=(SID=sid_name)))' =>
                array(
                    'host'        => 'host-name',
                    'sid'         => 'sid_name',
                    'servicename' => 'svc_name',
                    'dbname'      => 'db_name',
                ),
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=host-name)(PORT=1521))(CONNECT_DATA=(SID=sid_name)(INSTANCE_NAME=inst_name)))' =>
                array(
                    'host'         => 'host-name',
                    'sid'          => 'sid_name',
                    'servicename'  => 'svc_name',
                    'dbname'       => 'db_name',
                    'instancename' => 'inst_name',
                ),
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=host-name)(PORT=1521))(CONNECT_DATA=(SID=sid_name)(INSTANCE_NAME=inst_name)(SERVER=pooled)))' =>
                array(
                    'host'         => 'host-name',
                    'sid'          => 'sid_name',
                    'servicename'  => 'svc_name',
                    'dbname'       => 'db_name',
                    'instancename' => 'inst_name',
                    'pooled'       => true,
                ),
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=host-name)(PORT=1521))(CONNECT_DATA=(SID=sid_name)(INSTANCE_NAME=inst_name)(SERVER=shared)))' =>
                array(
                    'host'         => 'host-name',
                    'sid'          => 'sid_name',
                    'servicename'  => 'svc_name',
                    'dbname'       => 'db_name',
                    'instancename' => 'inst_name',
                    'server'       => 'shared',
                ),
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=host-name)(PORT=1521))(CONNECT_DATA=(SID=sid_name)(INSTANCE_NAME=inst_name)(SERVER=dedicated)))' =>
                array(
                    'host'         => 'host-name',
                    'sid'          => 'sid_name',
                    'servicename'  => 'svc_name',
                    'dbname'       => 'db_name',
                    'instancename' => 'inst_name',
                    'server'       => 'DEDICATED',
                    'pooled'       => true,
                ),
            '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=host-name)(PORT=1521))(CONNECT_DATA=(SID=sid_name)(SERVER=dedicated)))' =>
                array(
                    'host'        => 'host-name',
                    'sid'         => 'sid_name',
                    'servicename' => 'svc_name',
                    'dbname'      => 'db_name',
                    'server'      => 'dedicated',
                    'pooled'      => true,
                ),
        );

        $driver = $this->newDriver();

        $rf   = new \ReflectionClass($driver);
        $prop = $rf->getProperty('defaultParams');
        $prop->setAccessible(true);
        $defaults = $prop->getValue($driver);

        $method = $rf->getMethod('getConnectionString');
        $method->setAccessible(true);

        foreach ($tests as $expected => $params) {
            $params = array_merge($defaults, $params);
            $result = $method->invoke($driver, $params);
            $this->assertSame($expected, $result);
        }

        $this->setExpectedException('Develpup\Oci\OciException');

        $method->invoke(
            $driver,
            array_merge(
                $defaults,
                array(
                    'host'        => 'host-name',
                    'sid'         => 'sid_name',
                    'servicename' => 'svc_name',
                    'dbname'      => 'db_name',
                    'server'      => '__NOT_VALID__',
                    'pooled'      => true
                )
            )
        );
    }
}
