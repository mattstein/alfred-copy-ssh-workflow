<?php

use MattStein\CopySsh\SshKeyScanner;

describe('expandPath', function () {
    it('expands tilde to home directory', function () {
        $result = SshKeyScanner::expandPath('~/.ssh', '/Users/test');

        expect($result)->toBe('/Users/test/.ssh');
    });

    it('handles home directory with trailing slash', function () {
        $result = SshKeyScanner::expandPath('~/.ssh', '/Users/test/');

        expect($result)->toBe('/Users/test/.ssh');
    });

    it('leaves absolute paths unchanged', function () {
        $result = SshKeyScanner::expandPath('/etc/ssh', '/Users/test');

        expect($result)->toBe('/etc/ssh');
    });

    it('leaves relative paths without tilde unchanged', function () {
        $result = SshKeyScanner::expandPath('ssh/keys', '/Users/test');

        expect($result)->toBe('ssh/keys');
    });
});

describe('normalizePath', function () {
    it('adds trailing slash if missing', function () {
        $result = SshKeyScanner::normalizePath('/path/to/dir');

        expect($result)->toBe('/path/to/dir/');
    });

    it('keeps existing trailing slash', function () {
        $result = SshKeyScanner::normalizePath('/path/to/dir/');

        expect($result)->toBe('/path/to/dir/');
    });
});

describe('parseIgnoreList', function () {
    it('parses comma-separated list', function () {
        $result = SshKeyScanner::parseIgnoreList('config,known_hosts,authorized_keys');

        expect($result)->toBe(['config', 'known_hosts', 'authorized_keys']);
    });

    it('trims whitespace from items', function () {
        $result = SshKeyScanner::parseIgnoreList('config , known_hosts , authorized_keys');

        expect($result)->toBe(['config', 'known_hosts', 'authorized_keys']);
    });

    it('handles single item', function () {
        $result = SshKeyScanner::parseIgnoreList('config');

        expect($result)->toBe(['config']);
    });
});

describe('isPrivateKey', function () {
    it('detects OpenSSH private key', function () {
        $content = "-----BEGIN OPENSSH PRIVATE KEY-----\ntest\n-----END OPENSSH PRIVATE KEY-----";

        expect(SshKeyScanner::isPrivateKey($content))->toBeTrue();
    });

    it('detects RSA private key', function () {
        $content = "-----BEGIN RSA PRIVATE KEY-----\ntest\n-----END RSA PRIVATE KEY-----";

        expect(SshKeyScanner::isPrivateKey($content))->toBeTrue();
    });

    it('returns false for public key', function () {
        $content = "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQDTest123 test@example.com";

        expect(SshKeyScanner::isPrivateKey($content))->toBeFalse();
    });

    it('returns false for ed25519 public key', function () {
        $content = "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAITest456 test@example.com";

        expect(SshKeyScanner::isPrivateKey($content))->toBeFalse();
    });
});

describe('scan', function () {
    it('returns empty array for non-existent directory', function () {
        $result = SshKeyScanner::scan('/non/existent/path');

        expect($result)->toBe([]);
    });

    it('finds public keys in directory', function () {
        $result = SshKeyScanner::scan(fixturesPath('ssh'), SshKeyScanner::DEFAULT_IGNORE);

        expect($result)->toHaveCount(2);
        expect($result)->toContain([
            'filename' => 'id_ed25519.pub',
            'path' => fixturesPath('ssh') . '/id_ed25519.pub',
        ]);
        expect($result)->toContain([
            'filename' => 'id_rsa.pub',
            'path' => fixturesPath('ssh') . '/id_rsa.pub',
        ]);
    });

    it('excludes private keys', function () {
        $result = SshKeyScanner::scan(fixturesPath('ssh'), SshKeyScanner::DEFAULT_IGNORE);
        $filenames = array_column($result, 'filename');

        expect($filenames)->not->toContain('id_rsa');
    });

    it('excludes files in ignore list', function () {
        $result = SshKeyScanner::scan(fixturesPath('ssh'), SshKeyScanner::DEFAULT_IGNORE);
        $filenames = array_column($result, 'filename');

        expect($filenames)->not->toContain('config');
        expect($filenames)->not->toContain('known_hosts');
    });

    it('includes config when not in ignore list', function () {
        $result = SshKeyScanner::scan(fixturesPath('ssh'), ['.', '..']);
        $filenames = array_column($result, 'filename');

        expect($filenames)->toContain('config');
        expect($filenames)->toContain('known_hosts');
    });
});
