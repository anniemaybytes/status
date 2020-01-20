<?php declare(strict_types=1);

namespace RunTracy\Helpers;

use Tracy\IBarPanel;
use Twig\Profiler\Dumper\HtmlDumper;
use Twig\Profiler\Profile;

/**
 * Class TwigPanel
 *
 * @package RunTracy\Helpers
 */
class TwigPanel implements IBarPanel
{
    private $data;
    private $dumper;
    private $icon;
    private $ver;

    /**
     * TwigPanel constructor.
     *
     * @param Profile|null $data
     * @param array $ver
     */
    public function __construct(?Profile $data = null, array $ver = [])
    {
        $this->data = $data;
        $this->ver = $ver;
        $this->dumper = new HtmlDumper();
    }

    /**
     * @return string
     */
    public function getTab(): string
    {
        $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 532 178" style="width: 35px"><path ' .
            'fill="#016301" d="m439 153.67c-13.839-3.3534-27.492-13.852-34.543-26.563-1.9832-3.575-4.8271-10.55-' .
            '6.3198-15.5-2.4268-8.0476-2.7156-10.587-2.7288-24-0.0174-17.571 1.7519-25.761 8.271-38.287 7.21-13.853' .
            ' 17.154-22.854 31.034-28.09 5.3616-2.0225 8.3741-2.4659 17.015-2.5047 9.3728-0.04211 11.552 0.31335 ' .
            '20.304 3.3111l9.8037 3.3582 3.3143-3.2123c2.7653-2.6803 3.9534-3.1596 7.1733-2.8941 5.9886 0.49386 ' .
            '6.3732 2.0626 6.3902 26.068 0.0138 19.427-0.11274 20.877-1.9853 22.75-2.6489 2.6489-9.2839 ' .
            '2.7161-11.88 0.12034-1.0338-1.0338-2.6705-4.2963-3.6371-7.25-5.3036-16.207-11.229-21.047-25.983-21.' .
            '225-20.899-0.25197-32.47 13.929-34.151 41.854-1.319 21.913 4.6901 39.615 16.116 47.476 2.4245 1.6681 ' .
            '7.2492 3.7637 10.722 4.6569 5.4218 1.3946 7.4425 1.452 14.309 0.40628 11.564-1.7612 14.236-3.093 ' .
            '15.065-7.5096 0.99192-5.2874 0.012-14.554-1.6503-15.606-0.77555-0.49095-4.6514-0.89954-8.613-' .
            '0.90796-3.9616-0.008-8.3267-0.52734-9.7002-1.1532-5.9584-2.7148-5.9584-16.009 0-18.724 1.6402-' .
            '0.74732 10.562-1.1378 25.996-1.1378h23.498l2.4545 2.4545c1.8785 1.8785 2.4546 3.5102 2.4546 6.9529 ' .
            '0 7.4837-3.1941 11.593-9.0119 11.593-3.2852 0-3.9881 3.31-3.9881 18.779 0 17.395 0.22878 16.951-' .
            '10.673 20.742-14.636 5.0898-37.094 6.9404-49.055 4.0422zm-404.82-4.02c-2.0207-2.0207-2.4545-3.4428-' .
            '2.4545-8.0454 0-4.6027 0.43385-6.0248 2.4545-8.0454 2.3217-2.3217 3.0774-2.4546 13.965-2.4546 10.356 ' .
            '0 11.614-0.1939 12.545-1.9343 0.66469-1.242 1.0352-16.427 1.0352-42.429 0-35.48-0.1946-40.689-1.5714-' .
            '42.066-1.2145-1.2145-3.7696-1.568-11.25-1.5561-5.3232 0.0084-10.272 0.39185-10.998 0.85206-0.95307 ' .
            '0.60431-1.482 5.0616-1.9042 16.046-0.68883 17.923-1.5952 20.087-8.4145 20.087-8.9273 0-9.9695-2.9072' .
            '-9.2814-25.892 0.67558-22.567 1.3061-28.852 3.0718-30.618 2.251-2.251 100.45-2.2428 102.7 0.0085 ' .
            '1.8581 1.8581 2.4121 7.1319 3.1518 30.001 0.61349 18.967-0.0363 23.642-3.5478 25.521-2.9727 1.5909-' .
            '8.5601 1.1237-11.093-0.92762-2.2403-1.8141-2.3822-2.666-2.9275-17.578-0.4405-12.046-0.90466-15.881-' .
            '2.0063-16.578-1.9011-1.203-20.019-1.1689-21.933 0.04125-2.2616 1.4297-2.2616 84.613 0 86.042 0.825 ' .
            '0.52152 6.358 0.95511 12.295 0.96353 10.118 0.0144 10.949 0.16928 13.25 2.4699 2.0207 2.0207 2.4546 ' .
            '3.4428 2.4546 8.0454 0 4.6027-0.43385 6.0248-2.4546 8.0454l-2.4545 2.4546h-36.091-36.091l-2.4545-2.' .
            '4546zm126.07 1.0951c-1.6426-1.2011-2.4718-7.45-7.1186-53.646-3.779-37.569-5.6317-52.523-6.582-53.125-' .
            '0.72741-0.46121-2.3555-0.84545-3.618-0.85387-1.2625-0.0084-3.4-1.1199-4.75-2.4699-2.0581-2.0581-' .
            '2.4546-3.4192-2.4546-8.4274 0-5.3038 0.30366-6.2117 2.7106-8.1051 2.5798-2.0292 3.598-2.1172 21.09-' .
            '1.8226 17.933 0.30208 18.426 0.36698 20.289 2.6704 1.3557 1.6759 1.9096 3.9373 1.9096 7.7955 0 6.7358' .
            '-2.9062 10.344-8.3321 10.344-6.081 0-6.0609-0.15507-4.2408 32.735 1.8641 33.686 1.971 34.528 4.3278 ' .
            '34.074 1.3047-0.25126 3.1049-5.8352 8.0006-24.816 3.4744-13.471 7.1288-25.617 8.1207-26.992 4.246-5.' .
            '8855 13.324-6.3127 17.415-0.81944 1.1358 1.5254 4.884 13.377 8.6974 27.5 5.0092 18.552 7.1104 24.899 ' .
            '8.3209 25.136 2.3015 0.44975 2.4462-0.71522 4.239-34.125 1.7636-32.865 1.786-32.691-4.2161-32.691-5.' .
            '4258 0-8.3321-3.6079-8.3321-10.344 0-3.8581 0.55397-6.1195 1.9096-7.7955 1.8646-2.3051 2.3474-2.3681 ' .
            '20.484-2.6725l18.574-0.31167 2.5162 2.5162c2.0882 2.0882 2.5162 3.4672 2.5162 8.1071 0 4.6027-0.43385 ' .
            '6.0248-2.4546 8.0455-1.35 1.35-3.4875 2.4614-4.75 2.4699-4.9899 0.03329-4.8612-0.60019-9.8813 48.639-' .
            '6.0013 58.864-5.7693 57.058-7.5576 58.846-0.96731 0.96732-3.4419 1.4994-6.9734 1.4994-7.9687 0-9.1727-' .
            '1.6085-13.848-18.5-14.291-51.634-15.982-57-17.96-57-1.6844 0-3.1381 4.4932-11.628 35.942-7.8114 ' .
            '28.934-10.139 36.295-11.936 37.75-2.7106 2.1949-11.718 2.4736-14.488 0.44827zm126.11-0.71314c-2.3069' .
            '-1.8146-2.6349-2.7965-2.6349-7.8892 0-4.234 0.51958-6.459 1.9096-8.1774 1.7817-2.2027 2.7024-2.3818 ' .
            '13.75-2.6744 9.5021-0.25169 12.087-0.63504 13.09-1.9411 0.95218-1.2398 1.25-11.186 1.25-41.745 0-30.' .
            '56-0.29782-40.506-1.25-41.745-1.0031-1.3061-3.5884-1.6894-13.09-1.9411-11.048-0.29262-11.968-0.47171' .
            '-13.75-2.6744-1.39-1.7184-1.9096-3.9435-1.9096-8.1774 0-5.0927 0.32792-6.0746 2.6349-7.8892 2.5526-2.' .
            '0079 3.7368-2.0726 37.953-2.0726 24.715 0 36.084 0.34915 37.87 1.1629 5.6165 2.5591 5.2929 15.658-0.' .
            '45828 18.55-1.5386 0.77374-6.6434 1.2628-13.275 1.2719-10.64 0.01456-10.787 0.0468-11.75 2.5798-1.' .
            '2695 3.3391-1.2992 78.453-0.0323 81.785 0.92182 2.4246 1.2146 2.4875 13.091 2.8151 11.358 0.31327 ' .
            '12.272 0.48851 14.057 2.6958 2.9676 3.669 2.6615 12.478-0.54508 15.685l-2.4546 2.4546h-35.911c-34.' .
            '816 0-35.991-0.0632-38.545-2.0726z"/></svg>';
        return '
        <span title="Twig Info">' . $this->icon .
            '<span class="tracy-label">' . sprintf('%.2f ms', $this->data->getDuration() * 1000) . '</span>
        </span>';
    }

    /**
     * @return string
     */
    public function getPanel(): string
    {
        return '<h1>' . $this->icon . ' Twig ' . $this->ver['twig'] . '</h1>
        <div class="tracy-inner">
            <p>
                <table width="100%">
                    <thead><tr><th><b>Twig ' . $this->ver['twig'] . ' profiler result</b></th></tr></thead>
                    <tr class="yes"><th><b>' . $this->dumper->dump($this->data) . '</b></th></tr>
                </table>
            </p>
        </div>';
    }
}
