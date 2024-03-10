<?php

namespace pocketmine\utils;

/**
 * Class UUID
 * @package App\Utils
 */
class UUID
{
    private $parts = [0, 0, 0, 0];
    private $version = null;

    /**
     * UUID constructor.
     * @param int $part1
     * @param int $part2
     * @param int $part3
     * @param int $part4
     * @param int|null $version
     */
    public function __construct(int $part1 = 0, int $part2 = 0, int $part3 = 0, int $part4 = 0, int $version = null)
    {
        $this->parts[0] = $part1;
        $this->parts[1] = $part2;
        $this->parts[2] = $part3;
        $this->parts[3] = $part4;

        $this->version = $version === null ? ($this->parts[1] & 0xf000) >> 12 : $version;
    }

    /**
     * Get the version of the UUID.
     * @return int|null
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * Check if the UUID is equal to another UUID.
     * @param UUID $uuid
     * @return bool
     */
    public function equals(UUID $uuid): bool
    {
        return $uuid->parts[0] === $this->parts[0] && $uuid->parts[1] === $this->parts[1] && $uuid->parts[2] === $this->parts[2] && $uuid->parts[3] === $this->parts[3];
    }

    /**
     * Create an UUID from a hexadecimal representation.
     * @param string $uuid
     * @param int|null $version
     * @return UUID
     */
    public static function fromString(string $uuid, int $version = null): UUID
    {
        return self::fromBinary(hex2bin(str_replace("-", "", trim($uuid))), $version);
    }

    /**
     * Create an UUID from a binary representation.
     * @param string $uuid
     * @param int|null $version
     * @return UUID
     */
    public static function fromBinary(string $uuid, int $version = null): UUID
    {
        if (strlen($uuid) !== 16) {
            throw new \InvalidArgumentException("Must have exactly 16 bytes");
        }

        return new UUID(Binary::readInt(substr($uuid, 0, 4)), Binary::readInt(substr($uuid, 4, 4)), Binary::readInt(substr($uuid, 8, 4)), Binary::readInt(substr($uuid, 12, 4)), $version);
    }

    /**
     * Create an UUIDv3 from binary data or list of binary data.
     * @param string ...$data
     * @return UUID
     */
    public static function fromData(string ...$data): UUID
    {
        $hash = hash("md5", implode($data), true);

        return self::fromBinary($hash, 3);
    }

    /**
     * Create a random UUID.
     * @return UUID
     */
    public static function fromRandom(): UUID
    {
        return self::fromData(Binary::writeInt(time()), Binary::writeShort(getmypid()), Binary::writeShort(getmyuid()), Binary::writeInt(mt_rand(-0x7fffffff, 0x7fffffff)), Binary::writeInt(mt_rand(-0x7fffffff, 0x7fffffff)));
    }

    /**
     * Convert the UUID to a binary representation.
     * @return string
     */
    public function toBinary(): string
    {
        return Binary::writeInt($this->parts[0]) . Binary::writeInt($this->parts[1]) . Binary::writeInt($this->parts[2]) . Binary::writeInt($this->parts[3]);
    }

    /**
     * Convert the UUID to a string representation.
     * @return string
     */
    public function toString(): string
    {
        $hex = bin2hex(self::toBinary());

        if ($this->version !== null) {
            return substr($hex, 0, 8) . "-" . substr($hex, 8, 4) . "-" . hexdec($this->version) . substr($hex, 13, 3) . "-8" . substr($hex, 17, 3) . "-" . substr($hex, 20, 12);
        }

        return substr($hex, 0, 8) . "-" . substr($hex, 8, 4) . "-" . substr($hex, 12, 4) . "-" . substr($hex, 16, 4) . "-" . substr($hex, 20, 12);
    }

    /**
     * Convert the UUID to a string representation.
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}