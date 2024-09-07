<?php

declare(strict_types=1);

namespace RunTracy\Helpers;

use Tracy\IBarPanel;
use Twig\Profiler\Dumper\HtmlDumper;
use Twig\Profiler\Profile;

/**
 * Class TwigPanel
 *
 * @author 1f7.wizard@gmail.com
 * @package RunTracy\Helpers
 */
final class TwigPanel implements IBarPanel
{
    private Profile $data;
    private HtmlDumper $dumper;

    private string $icon;
    private string $version;

    public function __construct(Profile $data, string $version)
    {
        $this->data = $data;
        $this->version = $version;
        $this->dumper = new HtmlDumper();
    }

    public function getTab(): string
    {
        $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path ' .
            'fill="#4E910C" d="M8.932 22.492c.016-6.448-.971-11.295-5.995-11.619 4.69-.352 7.113 2.633 9.298 ' .
            '6.907C12.205 6.354 9.882 1.553 4.8 1.297c7.433.07 10.028 5.9 11.508 14.293 1.171-2.282 3.56-5.553 ' .
            '5.347-1.361-1.594-2.04-3.607-1.617-3.978 8.262H8.933z"></path></svg>';
        return '<span title="Twig Info">' . $this->icon . '<span class="tracy-label">' .
            sprintf('%.2f ms / %.2f MB', $this->data->getDuration() * 1000, $this->data->getMemoryUsage() / 1e+6) . '</span>
        </span>';
    }

    public function getPanel(): string
    {
        return '<h1>' . $this->icon . ' Twig ' . $this->version . '</h1>
        <div class="tracy-inner">
            <p>
                <table style="width: 100%;">
                    <thead><tr><th><b>Profiler result</b></th></tr></thead>
                    <tr class="yes"><th><b>' . $this->dumper->dump($this->data) . '</b></th></tr>
                </table>
            </p>
        </div>';
    }
}
