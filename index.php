<?php
declare(strict_types=1);

/**
 * Basic OOP in PHP: Classes
 * Interactive presentation. PHP stores the slide data and renders the HTML.
 * CSS handles the design, JavaScript handles navigation.
 */

/* ------------------------------------------------------------------
 * 1. Helper functions
 * ---------------------------------------------------------------- */

/** Escape text so it is safe to print inside HTML. */
function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/** Escape text, then turn `backtick` words into <code> elements. */
function md(string $text): string
{
    return preg_replace('/`([^`]+)`/', '<code>$1</code>', e($text));
}

/**
 * Tiny PHP syntax highlighter (VS Code Dark+ style colors).
 * It finds tokens with one regular expression and wraps each one in a <span>.
 * Returns an array: one highlighted HTML string per line of code.
 */
function highlight_php_line(string $line): string
{
    $pattern = '~(?<comment>//[^\n]*)'
        . '|(?<string>"(?:[^"\\\\]|\\\\.)*")'
        . '|(?<tag><\?php|\?>)'
        . '|(?<variable>\$[A-Za-z_]\w*)'
        . '|(?<keyword>\b(?:class|public|function|new|echo)\b)'
        . '|(?<arrow>->)'
        . '|(?<property>(?<=->)[A-Za-z_]\w*(?!\w|\())'
        . '|(?<classname>\b[A-Z]\w*\b)'
        . '|(?<func>\b[A-Za-z_]\w*(?=\())~';

    $groups = ['comment', 'string', 'tag', 'variable', 'keyword', 'arrow', 'property', 'classname', 'func'];

    preg_match_all($pattern, $line, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

    $html = '';
    $position = 0;

    foreach ($matches as $match) {
        $text = $match[0][0];
        $offset = $match[0][1];

        // Plain text before this token
        $html .= e(substr($line, $position, $offset - $position));

        $class = 'plain';
        foreach ($groups as $group) {
            if (isset($match[$group]) && $match[$group][1] >= 0 && $match[$group][0] !== '') {
                $class = $group;
                break;
            }
        }

        $html .= '<span class="tok-' . $class . '">' . e($text) . '</span>';
        $position = $offset + strlen($text);
    }

    return $html . e(substr($line, $position));
}

function highlight_php_lines(string $code): array
{
    return array_map('highlight_php_line', preg_split('/\R/', $code));
}

/* ------------------------------------------------------------------
 * 2. Slide content (PHP arrays)
 * ---------------------------------------------------------------- */

$meta = [
    'title' => 'Basic OOP in PHP: Classes',
    'file'  => 'index.php',
];

$carClass = <<<'PHP'
<?php
class Car {
    public $brand;

    public function startEngine() {
        echo "Engine started!";
    }
}
?>
PHP;

$carObject = <<<'PHP'
<?php
class Car {
    public $brand;

    public function startEngine() {
        echo "Engine started!";
    }
}

$car1 = new Car();
$car1->brand = "Toyota";
$car1->startEngine();
?>
PHP;

$studentExample = <<<'PHP'
<?php
class Student {
    public $name;

    public function introduce() {
        echo "Hello, my name is " . $this->name;
    }
}

$student = new Student();
$student->name = "Alex";
$student->introduce();
?>
PHP;

$slides = [
    // Slide 1
    [
        'type'     => 'intro',
        'label'    => 'Introduction',
        'eyebrow'  => 'Lesson 01 · PHP Fundamentals',
        'title'    => 'BASIC OOP IN PHP',
        'subtitle' => 'Understanding Classes and Objects',
        'command'  => 'php learn_oop.php',
        'tagline'  => 'Write cleaner code. Think in objects.',
    ],

    // Slide 2
    [
        'type'   => 'concept',
        'label'  => 'What Is OOP?',
        'topic'  => 'fundamentals',
        'title'  => 'What Is OOP?',
        'lead'   => 'Object-Oriented Programming (OOP) is a way of writing code that models real-world things.',
        'points' => [
            ['icon' => '{ }', 'title' => 'OOP organizes code around objects', 'text' => 'Related code lives together in one place, so programs are easier to read and maintain.'],
            ['icon' => '$ ()', 'title' => 'Objects combine data and behavior', 'text' => 'Data is stored in properties. Behavior is written as methods.'],
            ['icon' => 'cls', 'title' => 'Classes act as blueprints', 'text' => 'A class describes what an object has and can do. Many objects can be built from it.'],
        ],
        'analogy' => [
            'from'    => ['variant' => 'blueprint', 'label' => 'BLUEPRINT', 'name' => 'class Car', 'lines' => ['$brand', 'startEngine()']],
            'arrow'   => 'new',
            'to'      => ['variant' => 'object', 'label' => 'OBJECT', 'name' => '$car1 = new Car()', 'lines' => ['brand = "Toyota"', 'can call startEngine()']],
            'caption' => 'One blueprint can build many objects.',
        ],
    ],

    // Slide 3
    [
        'type'    => 'diagram',
        'label'   => 'Classes vs Objects',
        'topic'   => 'concepts',
        'title'   => 'Understanding Classes and Objects',
        'class'   => ['name' => 'Car', 'properties' => ['$brand'], 'methods' => ['startEngine()']],
        'objects' => [
            ['name' => '$car1', 'brand' => 'Toyota'],
            ['name' => '$car2', 'brand' => 'Honda'],
        ],
        'compare' => [
            ['term' => 'Class', 'text' => 'A blueprint. It defines the structure, but it is not a real car yet.'],
            ['term' => 'Object', 'text' => 'A real instance made from the class. Each object keeps its own data.'],
        ],
    ],

    // Slide 4
    [
        'type'    => 'code',
        'label'   => 'Creating a Class',
        'topic'   => 'syntax',
        'title'   => 'Creating a Class in PHP',
        'file'    => 'Car.php',
        'code'    => $carClass,
        'marks'   => [2 => 1, 3 => 2, 5 => 3, 6 => 4],
        'lead'    => 'A class is built from the `class` keyword, properties, and methods.',
        'notes'   => [
            ['title' => '`class Car`', 'text' => 'Starts a new class. Class names usually start with a capital letter.'],
            ['title' => '`public $brand;`', 'text' => 'A property: a variable that belongs to the class. `public` means it can be used from outside.'],
            ['title' => '`startEngine()`', 'text' => 'A method: a function inside a class. It defines what the object can do.'],
            ['title' => '`echo`', 'text' => 'Prints text. It only runs when the method is called.'],
        ],
        'callout' => 'Property = data. Method = behavior.',
        'output'  => null,
    ],

    // Slide 5
    [
        'type'    => 'code',
        'label'   => 'Creating Objects',
        'topic'   => 'instantiation',
        'title'   => 'Creating Objects',
        'file'    => 'index.php',
        'code'    => $carObject,
        'marks'   => [10 => 1, 11 => 2, 12 => 3],
        'lead'    => 'Use `new` to create an object and `->` to reach its property or method.',
        'notes'   => [
            ['title' => '`new Car()`', 'text' => 'Creates a new object from the `Car` class and stores it in the variable `$car1`.'],
            ['title' => '`$car1->brand = "Toyota";`', 'text' => 'The `->` operator accesses the object. Here it assigns a value to the `brand` property.'],
            ['title' => '`$car1->startEngine();`', 'text' => 'Calls the method. Its code runs and prints the output below.'],
        ],
        'callout' => null,
        'output'  => 'Engine started!',
    ],

    // Slide 6
    [
        'type'    => 'code',
        'label'   => 'A Practical Example',
        'topic'   => 'example',
        'title'   => 'A Practical Example',
        'file'    => 'student.php',
        'code'    => $studentExample,
        'marks'   => [6 => 1, 11 => 2, 12 => 3],
        'lead'    => 'A `Student` class with one property and one method.',
        'notes'   => [
            ['title' => '`$this->name`', 'text' => '`$this` is a reference to the current object. It lets a method read its own object\'s data.'],
            ['title' => '`$student->name = "Alex";`', 'text' => 'Stores "Alex" in this student object.'],
            ['title' => '`$student->introduce();`', 'text' => 'Runs the method. Inside it, `$this->name` is "Alex".'],
        ],
        'callout' => 'Think of `$this` as "me": when `$student` calls `introduce()`, `$this` means `$student`.',
        'output'  => 'Hello, my name is Alex',
    ],

    // Slide 7
    [
        'type'     => 'summary',
        'label'    => 'Summary',
        'topic'    => 'recap',
        'title'    => 'You Have Learned the Basics!',
        'items'    => [
            ['key' => 'class',      'text' => 'A class is a blueprint.'],
            ['key' => 'object',     'text' => 'An object is an instance of a class.'],
            ['key' => 'properties', 'text' => 'Properties store data.'],
            ['key' => 'methods',    'text' => 'Methods define behavior.'],
            ['key' => 'new',        'text' => '`new` creates an object.'],
            ['key' => '$this',      'text' => '`$this` refers to the current object.'],
        ],
        'quote'    => 'Every great developer starts with the basics.',
        'terminal' => [
            ['kind' => 'cmd',  'text' => 'php thank_you.php'],
            ['kind' => 'out',  'text' => 'Lesson complete: classes and objects.'],
            ['kind' => 'big',  'text' => 'Thank You!'],
            ['kind' => 'dim',  'text' => 'Process finished with exit code 0'],
        ],
    ],
];

$total = count($slides);

/* ------------------------------------------------------------------
 * 3. Rendering functions (one per slide type)
 * ---------------------------------------------------------------- */

function render_terminal(string $title, array $lines, bool $cursor = false, string $class = '', bool $replay = false): void
{ ?>
    <div class="terminal <?= e($class) ?>">
        <div class="terminal__bar">
            <span class="terminal__title"><?= e($title) ?></span>
            <?php if ($replay): ?>
                <button type="button" class="btn btn--small" data-replay>Replay output</button>
            <?php endif; ?>
        </div>
        <div class="terminal__body" data-typer<?= $cursor ? ' data-cursor' : '' ?>>
            <?php foreach ($lines as $line): ?>
                <div class="t-line t-line--<?= e($line['kind']) ?>"><span class="t-text" data-text="<?= e($line['text']) ?>"><?= e($line['text']) ?></span></div>
            <?php endforeach; ?>
        </div>
    </div>
<?php }

function render_code_window(string $file, string $code, array $marks): void
{
    $lines = highlight_php_lines($code); ?>
    <div class="code-window">
        <div class="code-window__bar">
            <span class="code-window__file"><?= e($file) ?></span>
            <span class="code-window__lang">PHP</span>
        </div>
        <pre class="code" tabindex="0"><code><?php
            foreach ($lines as $index => $html) {
                $number = $index + 1;
                $mark = $marks[$number] ?? null;
                echo '<span class="line' . ($mark ? ' line--marked' : '') . '">';
                echo '<span class="ln">' . $number . '</span>';
                echo '<span class="lc">' . $html . '</span>';
                if ($mark) {
                    echo '<span class="badge" aria-label="See note ' . $mark . '">' . $mark . '</span>';
                }
                echo '</span>';
            }
        ?></code></pre>
    </div>
<?php }

function render_analogy_card(array $card): void
{ ?>
    <div class="analogy__card analogy__card--<?= e($card['variant']) ?>">
        <span class="analogy__label"><?= e($card['label']) ?></span>
        <strong class="analogy__name"><?= e($card['name']) ?></strong>
        <ul>
            <?php foreach ($card['lines'] as $line): ?>
                <li><?= e($line) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php }

function render_intro(array $s): void
{ ?>
    <div class="intro">
        <div class="intro__text">
            <p class="eyebrow"><span>//</span> <?= e($s['eyebrow']) ?></p>
            <h1 class="intro__title"><?= e($s['title']) ?></h1>
            <p class="intro__subtitle"><?= e($s['subtitle']) ?></p>
            <p class="intro__tagline"><?= e($s['tagline']) ?></p>
        </div>
        <div class="intro__side">
            <?php render_terminal('bash: php-oop-presentation', [['kind' => 'cmd', 'text' => $s['command']]], true, 'terminal--hero'); ?>
            <p class="hint">Use <kbd>&larr;</kbd> <kbd>&rarr;</kbd> or the buttons below to navigate.</p>
        </div>
    </div>
<?php }

function render_concept(array $s): void
{ ?>
    <h2 class="slide__title"><?= e($s['title']) ?></h2>
    <p class="lead"><?= md($s['lead']) ?></p>
    <div class="split split--concept">
        <ul class="points">
            <?php foreach ($s['points'] as $point): ?>
                <li class="point">
                    <span class="point__icon"><?= e($point['icon']) ?></span>
                    <div>
                        <strong><?= e($point['title']) ?></strong>
                        <p><?= e($point['text']) ?></p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="analogy">
            <?php render_analogy_card($s['analogy']['from']); ?>
            <div class="analogy__arrow" aria-hidden="true"><code><?= e($s['analogy']['arrow']) ?></code><span>&darr;</span></div>
            <?php render_analogy_card($s['analogy']['to']); ?>
            <p class="analogy__caption"><?= e($s['analogy']['caption']) ?></p>
        </div>
    </div>
<?php }

function render_diagram(array $s): void
{ ?>
    <h2 class="slide__title"><?= e($s['title']) ?></h2>
    <div class="diagram">
        <div class="diagram__class">
            <span class="analogy__label">CLASS</span>
            <strong class="analogy__name"><?= e($s['class']['name']) ?></strong>
            <ul>
                <?php foreach ($s['class']['properties'] as $property): ?>
                    <li><span class="dim">property</span> <?= e($property) ?></li>
                <?php endforeach; ?>
                <?php foreach ($s['class']['methods'] as $method): ?>
                    <li><span class="dim">method</span> <?= e($method) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="diagram__links" aria-hidden="true">
            <svg viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0 50 C 50 50, 50 25, 100 25" />
                <path d="M0 50 C 50 50, 50 75, 100 75" />
            </svg>
            <code>new Car()</code>
        </div>
        <div class="diagram__objects">
            <?php foreach ($s['objects'] as $object): ?>
                <div class="diagram__object">
                    <span class="analogy__label">OBJECT</span>
                    <strong class="analogy__name"><?= e($object['name']) ?></strong>
                    <p>brand = "<?= e($object['brand']) ?>"</p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="compare">
        <?php foreach ($s['compare'] as $item): ?>
            <div class="compare__item">
                <strong><?= e($item['term']) ?></strong>
                <p><?= e($item['text']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php }

function render_code_slide(array $s): void
{ ?>
    <h2 class="slide__title"><?= e($s['title']) ?></h2>
    <div class="split split--code">
        <div class="split__code">
            <?php
            render_code_window($s['file'], $s['code'], $s['marks']);
            if (!empty($s['output'])) {
                render_terminal('output: ' . $s['file'], [
                    ['kind' => 'cmd', 'text' => 'php ' . $s['file']],
                    ['kind' => 'out', 'text' => $s['output']],
                ], false, 'terminal--output', true);
            }
            ?>
        </div>
        <div class="split__info">
            <p class="lead"><?= md($s['lead']) ?></p>
            <ol class="notes">
                <?php foreach ($s['notes'] as $index => $note): ?>
                    <li>
                        <span class="badge"><?= $index + 1 ?></span>
                        <div>
                            <strong><?= md($note['title']) ?></strong>
                            <p><?= md($note['text']) ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
            <?php if (!empty($s['callout'])): ?>
                <p class="callout"><?= md($s['callout']) ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php }

function render_summary(array $s): void
{ ?>
    <h2 class="slide__title"><?= e($s['title']) ?></h2>
    <div class="split split--summary">
        <ul class="recap">
            <?php foreach ($s['items'] as $item): ?>
                <li><code class="recap__key"><?= e($item['key']) ?></code><span><?= md($item['text']) ?></span></li>
            <?php endforeach; ?>
        </ul>
        <div class="closing">
            <blockquote class="quote">&ldquo;<?= e($s['quote']) ?>&rdquo;</blockquote>
            <?php render_terminal('bash: closing', $s['terminal'], true, 'terminal--closing'); ?>
        </div>
    </div>
<?php }

function render_slide(array $slide, int $number, int $total): void
{ ?>
    <section class="slide slide--<?= e($slide['type']) ?><?= $number === 1 ? ' is-active' : '' ?>"
             id="slide-<?= $number ?>"
             data-title="<?= e($slide['label']) ?>"
             aria-label="Slide <?= $number ?> of <?= $total ?>: <?= e($slide['label']) ?>">
        <div class="slide__inner">
            <?php if (isset($slide['topic'])): ?>
                <p class="eyebrow"><span>//</span> <?= sprintf('%02d', $number) ?> · <?= e($slide['topic']) ?></p>
            <?php endif; ?>
            <?php
            switch ($slide['type']) {
                case 'intro':   render_intro($slide);      break;
                case 'concept': render_concept($slide);    break;
                case 'diagram': render_diagram($slide);    break;
                case 'code':    render_code_slide($slide); break;
                case 'summary': render_summary($slide);    break;
            }
            ?>
        </div>
    </section>
<?php }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($meta['title']) ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
<div class="app" id="app">

    <header class="topbar">
        <div class="topbar__left">
            <span class="dots" aria-hidden="true"><i></i><i></i><i></i></span>
            <span class="tab"><span class="tab__icon">&lt;?</span><?= e($meta['file']) ?></span>
            <span class="topbar__title"><?= e($meta['title']) ?></span>
        </div>
        <button type="button" class="btn" id="fullscreen-btn" aria-pressed="false">
            <span aria-hidden="true">[ ]</span> <span class="btn__label">Fullscreen</span>
        </button>
    </header>

    <main class="stage" id="stage">
        <?php foreach ($slides as $index => $slide): ?>
            <?php render_slide($slide, $index + 1, $total); ?>
        <?php endforeach; ?>
    </main>

    <footer class="controls">
        <div class="progress" id="progress" role="progressbar" aria-label="Presentation progress"
             aria-valuemin="1" aria-valuemax="<?= $total ?>" aria-valuenow="1">
            <div class="progress__bar" id="progress-bar" style="width: <?= round(100 / $total, 2) ?>%"></div>
        </div>
        <div class="controls__row">
            <button type="button" class="btn" id="prev-btn" aria-label="Previous slide"><span aria-hidden="true">&larr;</span> Prev</button>

            <nav class="slide-nav" aria-label="Slide navigation">
                <ol>
                    <?php foreach ($slides as $index => $slide): ?>
                        <li>
                            <button type="button" class="dot-btn<?= $index === 0 ? ' is-active' : '' ?>"
                                    data-goto="<?= $index ?>"
                                    aria-label="Go to slide <?= $index + 1 ?>: <?= e($slide['label']) ?>"
                                    title="<?= e($slide['label']) ?>"></button>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>

            <div class="counter" aria-live="polite">
                <span id="counter"><?= sprintf('%02d / %02d', 1, $total) ?></span>
                <span class="counter__label" id="slide-label"><?= e($slides[0]['label']) ?></span>
            </div>

            <button type="button" class="btn btn--primary" id="next-btn" aria-label="Next slide">Next <span aria-hidden="true">&rarr;</span></button>
        </div>
    </footer>

</div>
</body>
</html>