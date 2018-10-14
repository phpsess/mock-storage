<?php

declare(strict_types=1);

namespace PHPSess\Tests;

use PHPSess\Storage\MockStorage;

use PHPUnit\Framework\TestCase;
use PHPSess\Exception\SessionNotFoundException;

use Exception;

final class MockStorageTest extends TestCase
{

    /**
     * @covers \PHPSess\Storage\MockStorage::save
     * @covers \PHPSess\Storage\MockStorage::get
     */
    public function testSaveThenGet()
    {
        $storage = new MockStorage();

        $identifier = $this->getName();

        $data = 'test_data';

        $storage->save($identifier, $data);

        $saved_data = $storage->get($identifier);

        $this->assertEquals($data, $saved_data);
    }

    /**
     * @covers \PHPSess\Storage\MockStorage::get
     */
    public function testGetWithDifferentInstance()
    {
        $storage = new MockStorage();

        $identifier = $this->getName();

        $data = 'test_data';

        $storage->save($identifier, $data);

        $new_file_storage = new MockStorage();

        $saved_data = $new_file_storage->get($identifier);

        $this->assertEquals($data, $saved_data);
    }

    /**
     * @covers \PHPSess\Storage\MockStorage::get
     */
    public function testGetInexistent()
    {
        $storage = new MockStorage();

        $identifier = $this->getName();

        $this->expectException(SessionNotFoundException::class);

        $storage->get($identifier);
    }

    /**
     * @covers \PHPSess\Storage\MockStorage::sessionExists
     */
    public function testExists()
    {
        $storage = new MockStorage();

        $identifier = $this->getName();

        $exists = $storage->sessionExists($identifier);

        $this->assertFalse($exists);

        $storage->save($identifier, 'test');

        $exists = $storage->sessionExists($identifier);

        $this->assertTrue($exists);
    }

    /**
     * @covers \PHPSess\Storage\MockStorage::destroy
     */
    public function testDestroy()
    {
        $storage = new MockStorage();

        $identifier = $this->getName();

        $storage->save($identifier, 'test');

        $exists = $storage->sessionExists($identifier);

        $this->assertTrue($exists);

        $storage->destroy($identifier);

        $exists = $storage->sessionExists($identifier);

        $this->assertFalse($exists);
    }

    /**
     * @covers \PHPSess\Storage\MockStorage::destroy
     */
    public function testDestroyInexistent()
    {
        $storage = new MockStorage();

        $identifier = $this->getName();

        $this->expectException(SessionNotFoundException::class);

        $storage->destroy($identifier);
    }

    /**
     * @covers \PHPSess\Storage\MockStorage::clearOld
     */
    public function testClearOld()
    {
        $storage = new MockStorage();

        $identifier = $this->getName();

        $storage->save($identifier, 'test');

        usleep(1000); // 1 milisecond

        $exists = $storage->sessionExists($identifier);

        $this->assertTrue($exists);

        $storage->clearOld(10);

        $exists = $storage->sessionExists($identifier);

        $this->assertFalse($exists);
    }

    /**
     * @covers \PHPSess\Storage\MockStorage::clearOld
     */
    public function testDoNotClearNew()
    {
        $storage = new MockStorage();

        $identifier = $this->getName();

        $storage->save($identifier, 'test');

        $exists = $storage->sessionExists($identifier);

        $this->assertTrue($exists);

        $storage->clearOld(1000000); // one second

        $exists = $storage->sessionExists($identifier);

        $this->assertTrue($exists);
    }

    /**
     * @covers \PHPSess\Storage\MockStorage::lock
     */
    public function testCanLockOnce()
    {
        $storage = new MockStorage();

        $identifier = $this->getName();

        $locked = $storage->lock($identifier);

        $this->assertTrue($locked);
    }

    /**
     * @covers \PHPSess\Storage\MockStorage::lock
     */
    public function testCantLockTwice()
    {
        $storage = new MockStorage();

        $identifier = $this->getName();

        $storage->lock($identifier);

        $locked = $storage->lock($identifier);

        $this->assertFalse($locked);
    }

    /**
     * @covers \PHPSess\Storage\MockStorage::lock
     * @covers \PHPSess\Storage\MockStorage::unlock
     */
    public function testCanLockUnlockAndLockAgain()
    {
        $storage = new MockStorage();

        $identifier = $this->getName();

        $storage->lock($identifier);

        $storage->unlock($identifier);

        $locked = $storage->lock($identifier);

        $this->assertTrue($locked);
    }

    /**
     * @covers \PHPSess\Storage\MockStorage::unlock
     */
    public function testUnlockInexistentThrowNoErrors()
    {
        $storage = new MockStorage();

        $identifier = $this->getName();

        $exception = null;
        try {
            $storage->unlock($identifier);
        } catch (Exception $exception) {
        }

        $this->assertNull($exception);
    }
}
