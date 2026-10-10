<?php

/**
 * "How to use the TMS" guides, one per role (TMS User Guide - StorageMart IT Department).
 * Each guide is a list of slides drawn by app/Views/guide/role_guide.php in the IT
 * Department's design (navy and orange). Shown the first time someone signs in, from the "?"
 * button in the header, and in the manuals library (/manuals).
 *
 * Slide types: cover, cards, roles, flow, statuses, steps (numbered steps with a picture of the
 * screen), priority, write, recap, thanks. A screen picture is described with:
 *   'screen' => ['url' => '/aom/tickets', 'menu' => 'Tickets', 'title' => 'Tickets',
 *                'buttons' => [['Bulk Transfer', 3], 'My Ticket'],          // [label, pin number]
 *                'top' => ['bullhorn' => 2],                                // header icons with a pin
 *                'blocks' => [['filters', ['Status', ['Branch', 2]]], ['table', ['Ticket #', 'Status'], 1]]]
 * Block kinds: filters, table, form, cards, chat, buttons, note, switch, list.
 */
class TmsGuide
{
    public const ROLES = [
        'EMPLOYEE' => ['label' => 'Employee', 'icon' => 'fa-user', 'home' => '/employee/dashboard', 'area' => '/employee'],
        'HEAD' => ['label' => 'Department Head', 'icon' => 'fa-user-tie', 'home' => '/head/dashboard', 'area' => '/head'],
        'HR' => ['label' => 'Human Resources (HR)', 'icon' => 'fa-id-badge', 'home' => '/hr/dashboard', 'area' => '/hr'],
        'IT' => ['label' => 'IT Personnel', 'icon' => 'fa-tools', 'home' => '/it', 'area' => '/it'],
        'AOM' => ['label' => 'Area Operations Manager (AOM)', 'icon' => 'fa-users', 'home' => '/aom/dashboard', 'area' => '/aom'],
        'OM' => ['label' => 'Operations Manager (OM)', 'icon' => 'fa-user-cog', 'home' => '/om/dashboard', 'area' => '/om'],
        'HOM' => ['label' => 'Head of Operations (HOM)', 'icon' => 'fa-sitemap', 'home' => '/hom/dashboard', 'area' => '/hom'],
        'ADMIN' => ['label' => 'Administrator', 'icon' => 'fa-user-shield', 'home' => '/admin', 'area' => '/admin'],
    ];

    /** Left menu of each role (for the screen pictures). */
    public const MENUS = [
        'EMPLOYEE' => ['Dashboard', 'My Tickets', 'Create Ticket', 'Assets', 'My Issued Items'],
        'HEAD' => ['Dashboard', 'My Tickets', 'Create Ticket', 'Assets', 'My Issued Items', 'Employees', 'Announcements'],
        'HR' => ['Dashboard', 'Employees', 'Inventory', 'Lost / Damaged Reports', 'My Issued Items', 'Tickets'],
        'IT' => ['Dashboard', 'Ticket', 'My Assets', 'My Issued Items', 'Employee Uploads', 'My Ratings', 'Assets for Replacement', 'Troubleshooting Guides'],
        'AOM' => ['Dashboard', 'Employees', 'Assets', 'My Issued Items', 'Tickets', 'My Ticket', 'Employee Ticket'],
        'OM' => ['Dashboard', 'Employees', 'Assets', 'My Issued Items', 'AOM Branches', 'Tickets', 'My Ticket', 'Employee Ticket'],
        'HOM' => ['Dashboard', 'Employees', 'Assets', 'My Issued Items', 'AOM Branches', 'Tickets', 'My Ticket', 'Employee Ticket', 'Announcements'],
        'ADMIN' => ['Dashboard', 'Users', 'Ticket', 'Assets Directory', 'My Issued Items', 'System', 'Audit Trail', 'Ratings Report', 'Ticket Report'],
    ];

    /** Role of the signed-in person (the Admin's own role while a full-access Admin opens other areas). */
    public static function currentRole(): string
    {
        $role = strtoupper((string) ($_SESSION['superuser_real_usertype'] ?? $_SESSION['usertype'] ?? ''));
        return isset(self::ROLES[$role]) ? $role : 'EMPLOYEE';
    }

    public static function exists(string $role): bool
    {
        return isset(self::ROLES[strtoupper($role)]);
    }

    public static function title(string $role): string
    {
        return 'TMS User Guide · ' . self::ROLES[$role]['label'];
    }

    /** IT and Admin can open every role's guide (to help others); everyone else only their own. */
    public static function canOpen(string $viewerRole, string $guideRole): bool
    {
        return $viewerRole === $guideRole || in_array($viewerRole, ['IT', 'ADMIN'], true);
    }

    /** @return array<int, array<string, mixed>> */
    public static function slides(string $role): array
    {
        $role = self::exists($role) ? strtoupper($role) : 'EMPLOYEE';
        $a = self::ROLES[$role]['area'];
        $s = [];
        $s[] = ['type' => 'cover', 'subtitle' => self::coverText($role)];
        $s[] = ['type' => 'cards', 'title' => 'What you can do in the TMS', 'lead' => self::whatLead($role), 'cards' => self::whatCards($role)];
        $s[] = ['type' => 'roles'];
        $s[] = self::signIn($role);
        $s[] = self::header($role);
        $s[] = self::passwordSlide($role);

        switch ($role) {
            case 'EMPLOYEE':
                $s[] = self::dashboard($role, $a . '/dashboard', 'Dashboard', [
                    ['Summary cards', 'How many assets you have, your tickets, and how many are pending or resolved.'],
                    ['Quick buttons', 'My Assets, My Tickets, Create Ticket and My Profile.'],
                    ['Charts', 'Your ticket status and resolution charts fill in once you have tickets.'],
                ], ['cards', ['Your assets', 'Total tickets', 'Pending', 'Resolved'], 1], ['buttons', ['My Assets', 'My Tickets', 'Create Ticket', 'My Profile'], 2]);
                $s[] = ['type' => 'flow'];
                $s[] = ['type' => 'statuses'];
                $s = array_merge($s, self::ticketSlides($role, $a . '/tickets/create', 'Create Ticket', 'Submit Ticket'));
                $s[] = self::trackTickets($role, $a . '/tickets', 'My Tickets');
                $s[] = self::insideTicket($role);
                $s[] = self::closeLoop($role);
                $s[] = self::assets($role, $a . '/assets', 'Assets', 'My Assets');
                $s[] = self::issuedItems($role, $a . '/items');
                break;

            case 'HEAD':
                $s[] = self::dashboard($role, $a . '/dashboard', 'Head Dashboard', [
                    ['My Dashboard', 'Your own tickets and assets.'],
                    ['My Department', 'The tickets of everyone in your department, with View All Tickets.'],
                    ['Quick buttons', 'View Tickets, My Assets and Employees.'],
                ], ['cards', ['My tickets', 'My assets', 'Department tickets', 'Pending'], 1], ['buttons', ['View Tickets', 'My Assets', 'Employees'], 3]);
                $s[] = ['type' => 'flow'];
                $s[] = ['type' => 'statuses'];
                $s = array_merge($s, self::ticketSlides($role, $a . '/tickets/create', 'Create Ticket', 'Submit Ticket'));
                $s[] = self::steps('Department tickets', 'See and follow every ticket of your department.', [
                    ['Open My Tickets', 'In the menu, click My Tickets. The list shows the tickets of your whole department.'],
                    ['Filter', 'Choose a Status or Priority, or type a ticket number in Search. Reset clears the filters.'],
                    ['Open a ticket', 'Click the eye button to see its details, history and chat.'],
                ], ['url' => $a . '/tickets', 'menu' => 'My Tickets', 'title' => 'Department Tickets', 'buttons' => ['Dashboard', 'Create New Ticket'],
                    'blocks' => [['filters', [['Status', 2], 'Priority', 'Search']], ['table', ['Ticket #', 'Employee', 'Priority', 'Status'], 3]]], [$a . '/tickets', 'Open department tickets']);
                $s[] = self::insideTicket($role);
                $s[] = self::closeLoop($role);
                $s[] = self::steps('Department employees', 'Everyone in your department, with their tickets and assets.', [
                    ['Open Employees', 'In the menu, click Employees.'],
                    ['Filter', 'Choose a Branch or Position. Clear Filters shows everyone again.'],
                    ['Tickets / Assets', 'On a row, click Tickets to see that person\'s tickets, or Assets to see the equipment assigned to them.'],
                ], ['url' => $a . '/employee', 'menu' => 'Employees', 'title' => 'Department Employees',
                    'blocks' => [['filters', [['Branch', 2], 'Position']], ['table', ['Employee', 'Branch', 'Email', 'Actions'], 3]]], [$a . '/employee', 'Open Employees']);
                $s[] = self::announcementPost($role, $a . '/announcements', 'Department Head');
                $s[] = self::assets($role, $a . '/assets', 'Assets', 'My Assets');
                $s[] = self::issuedItems($role, $a . '/items');
                break;

            case 'HR':
                $s[] = self::dashboard($role, $a . '/dashboard', 'HR Dashboard', [
                    ['Quick buttons', 'View Employees, Manage Inventory, Add Item and Assign Item.'],
                    ['Item Coverage and Recent Activity', 'How many employees have their items, and what was issued or returned in the last 7 days.'],
                    ['Employees with Assigned Items', 'Who has items and how many. Click View for the details.'],
                ], ['buttons', ['View Employees', 'Manage Inventory', 'Add Item', 'Assign Item'], 1], ['table', ['Employee', 'Items', 'Count', 'Action'], 3]);
                $s[] = self::steps('Employee records', 'The list of all employees from HR.', [
                    ['Search', 'Type a name or email and click Search.'],
                    ['Filter and sort', 'Choose the Department, Branch and Status (Active / Inactive), and how to sort. Click Apply Filters.'],
                    ['View', 'Opens the employee\'s details: personal details, issued items and history.'],
                    ['Form', 'Opens the accountability form of the items issued to that employee, ready to print and sign.'],
                ], ['url' => $a . '/employees', 'menu' => 'Employees', 'title' => 'Employees',
                    'blocks' => [['filters', [['Search', 1], ['Department', 2], 'Branch', 'Status']], ['table', ['Name', 'Department', 'Issued Items', 'Actions'], 3]]], [$a . '/employees', 'Open Employees']);
                $s[] = self::steps('Item inventory (uniforms and other items)', 'Keep the stock of every item HR issues.', [
                    ['Add New Item', 'Item type, size, color, quantity in stock, reorder level and cost. Low stock shows in the Stock Status column.'],
                    ['Assign Item to Employee', 'Choose the employee, the item, size and quantity. The stock goes down by itself.'],
                    ['Download Summary / Import from Excel', 'Download the stock list, or add many items or stock counts at once from Excel. An import is sent for approval first.'],
                    ['Edit, Discontinue, Reactivate, Delete', 'Use the buttons on each row. Discontinued items are no longer issued; Delete permanently removes the item.'],
                ], ['url' => $a . '/uniforms', 'menu' => 'Inventory', 'title' => 'Item Inventory',
                    'buttons' => [['Add New Item', 1], ['Assign Item to Employee', 2], ['Download Summary', 3], 'Import from Excel'],
                    'blocks' => [['table', ['Item', 'In Stock', 'Stock Status', 'Issued', 'Actions'], 4]]], [$a . '/uniforms', 'Open Inventory']);
                $s[] = self::steps('Assign from Excel and approvals', 'Issue items to many employees at once. Excel uploads are approved before anything changes.', [
                    ['Fill in the template', 'Assign from Excel > Download Template. One row per employee and item: Employee Email, Item Type, Size, Color, Quantity.'],
                    ['Upload and check', 'The preview shows every row. Rows with a problem (employee not found, item discontinued, not enough stock) are skipped.'],
                    ['Send for approval', 'Nothing is issued yet. The other HR accounts and the Admin are notified.'],
                    ['Approve or reject', 'Another HR account or the Admin opens Approvals and approves (items are issued, stock goes down) or rejects with a reason. You cannot approve your own upload.'],
                    ['Employees confirm', 'Each employee gets a notification and clicks "Confirm received". The Received column shows who has confirmed.'],
                ], ['url' => $a . '/uniforms/approvals', 'menu' => 'Inventory', 'title' => 'Approvals',
                    'buttons' => [['Assign from Excel', 1], 'Import from Excel', ['Approvals', 4]],
                    'blocks' => [['table', ['Type', 'What', 'Sent by', 'Status'], 3], ['buttons', [['Approve', 4], 'Reject']], ['table', ['Employee', 'Item', 'Received'], 5]]], [$a . '/uniforms/approvals', 'Open Approvals']);
                $s[] = self::steps('Lost / Damaged reports', 'When an employee reports an issued item as lost or damaged.', [
                    ['Open the reports', 'Click Lost / Damaged Reports in the menu (the number shows how many are waiting).'],
                    ['Check the report', 'Read the item, the employee\'s explanation and the date. Pending ones need your answer.'],
                    ['Decide', 'Confirm accepts the report. Item OK means the item is fine. Mark damaged / Mark lost records the item\'s condition.'],
                ], ['url' => $a . '/uniforms/reports', 'menu' => 'Lost / Damaged Reports', 'title' => 'Lost / Damaged Reports',
                    'buttons' => ['All', ['Pending', 1], 'Confirmed', 'Item OK'],
                    'blocks' => [['table', ['Employee', 'Item', 'Report', 'Status'], 2], ['buttons', ['Confirm', 'Item OK', 'Mark damaged', 'Mark lost'], 3]]], [$a . '/uniforms/reports', 'Open the reports']);
                $s[] = ['type' => 'flow'];
                $s = array_merge($s, self::ticketSlides($role, $a . '/tickets/create', 'Tickets', 'Submit Ticket'));
                $s[] = self::trackTickets($role, $a . '/tickets', 'Tickets');
                $s[] = self::insideTicket($role);
                $s[] = self::closeLoop($role);
                $s[] = self::issuedItems($role, $a . '/items');
                break;

            case 'IT':
                $s[] = self::steps('Your dashboard and "I\'m away"', 'Start here every day.', [
                    ['Available or away', 'Click "I\'m away (absent / leave)" when you are absent. You get no new tickets, and your unopened ones move to another IT person. Click again when you are back.'],
                    ['Quick buttons', 'All Tickets, My Assets, Employee Uploads and My Ratings.'],
                    ['Charts', 'Your tickets by status and how fast they were resolved.'],
                ], ['url' => '/it', 'menu' => 'Dashboard', 'title' => 'IT Dashboard', 'buttons' => [['All Tickets', 2], 'My Assets', 'Employee Uploads', 'My Ratings'],
                    'blocks' => [['switch', 'You are Available for new tickets', 1], ['cards', ['Assigned', 'In Progress', 'Resolved', 'Rating'], 3]]], ['/it', 'Open the dashboard']);
                $s[] = ['type' => 'flow'];
                $s[] = ['type' => 'statuses'];
                $s[] = self::steps('Your tickets', 'New tickets are assigned to you by branch zone, by the Saturday duty rotation, or by the Admin.', [
                    ['Open Ticket', 'In the menu, click Ticket. You see the tickets assigned to you.'],
                    ['Open it quickly', 'A ticket you do not open within the time set by the Admin moves to the next IT person.'],
                    ['Notifications', 'The bell shows new tickets, chat messages and "Need help" requests.'],
                ], ['url' => '/it/tickets', 'menu' => 'Ticket', 'title' => 'All Filed Tickets', 'top' => ['bell' => 3],
                    'blocks' => [['table', ['Ticket', 'Concern', 'Branch', 'Priority', 'Status'], 1]]], ['/it/tickets', 'Open your tickets'],
                    'Saturdays: the Admin sets who is on duty each month. On your Saturday every new ticket comes to you; when someone else is on duty, they get them.');
                $s[] = self::steps('Work on a ticket', 'Everything is on the ticket page.', [
                    ['Priority', 'Only IT can change it. Set it to how urgent the problem really is.'],
                    ['Action Taken and Resolution Details', 'Write what you did and the result for the requester.'],
                    ['Save Changes', 'Saves your notes; the status stays the same.'],
                    ['Mark as Done', 'Saves your notes and resolves the ticket. The technical report is made, and the requester is asked to sign and rate.'],
                ], ['url' => '/it/tickets/view?id=…', 'menu' => 'Ticket', 'title' => 'Ticket Details',
                    'blocks' => [['form', [['Priority', 1], ['Action Taken', 2], 'Resolution Details']], ['buttons', [['Save Changes', 3], ['Mark as Done', 4]]]]]);
                $s[] = self::steps('Chat and troubleshooting guides', 'Help the requester before you go on site.', [
                    ['Comments / chat', 'Ask questions and send photos. Ctrl+V pastes a screenshot. The chat bubble at the bottom right has all your open tickets.'],
                    ['Send in chat', 'Under "Troubleshooting guides for this ticket", click Send in chat to send the best guide (slides and PowerPoint).'],
                    ['Need help from IT personnel', 'When the guide did not help, the requester presses this button and you get a notification.'],
                ], ['url' => '/it/tickets/view?id=…', 'menu' => 'Ticket', 'title' => 'Ticket Details',
                    'blocks' => [['chat', 1], ['buttons', [['Send in chat', 2], ['Need help from IT personnel', 3]]]]]);
                $s[] = self::steps('Technical report and signatures', 'Close the paperwork on the screen.', [
                    ['Report PDF / Word', 'Download the technical report of a resolved ticket.'],
                    ['Sign report', 'Sign on the screen. The requester signs too, or uploads a signed paper copy.'],
                    ['Employee Uploads', 'Signed reports uploaded by employees, grouped by date. Search and Download them here.'],
                ], ['url' => '/it/uploads', 'menu' => 'Employee Uploads', 'title' => 'Employee Technical Reports',
                    'buttons' => [['Report PDF', 1], 'Report Word', ['Sign report', 2]], 'blocks' => [['filters', ['Search', 'Date', 'Employee'], 3], ['list', ['STM-20261009-0004 · signed report.pdf', 'STM-20261008-0002 · signed report.jpg']]]], ['/it/uploads', 'Open Employee Uploads']);
                $s[] = self::steps('Ratings, replacements and assets', 'Other pages in your menu.', [
                    ['My Ratings', 'The scores and comments requesters gave you, with the breakdown per star.'],
                    ['Assets for Replacement', 'Items that are 5 or more years old (from the Year Purchased), to plan replacements.'],
                    ['My Assets / My Issued Items', 'The equipment and items assigned to you.'],
                ], ['url' => '/it/ratings', 'menu' => 'My Ratings', 'title' => 'My Ratings',
                    'blocks' => [['cards', ['Average', '5 stars', '4 stars', 'Comments'], 1], ['table', ['Ticket', 'Rating', 'Comment'], 1]]], ['/it/ratings', 'Open My Ratings']);
                $s[] = self::manualsEditor($role, '/it/manuals');
                $s[] = self::insideTicket($role);
                break;

            case 'AOM':
            case 'OM':
            case 'HOM':
                $s = array_merge($s, self::operationsSlides($role, $a));
                break;

            case 'ADMIN':
                $s = array_merge($s, self::adminSlides());
                break;
        }

        $s[] = self::announcementsSlide($role);
        $s[] = self::birthdaysSlide($role);
        if ($role !== 'ADMIN') {
            $s[] = self::chatRequests();
        }
        $s[] = self::manualsLibrary($role);
        $s[] = ['type' => 'recap', 'items' => self::recapItems($role)];
        $s[] = ['type' => 'thanks'];
        return $s;
    }

    // ------------------------------------------------------------------ shared slides

    private static function steps(string $title, string $lead, array $steps, ?array $screen = null, ?array $try = null, string $note = ''): array
    {
        return ['type' => 'steps', 'title' => $title, 'lead' => $lead, 'steps' => $steps, 'screen' => $screen, 'try' => $try, 'note' => $note];
    }

    private static function coverText(string $role): string
    {
        $texts = [
            'EMPLOYEE' => 'A user guide for filing, tracking, and closing your IT concerns',
            'HEAD' => 'A user guide for your tickets, your department\'s tickets and announcements',
            'HR' => 'A user guide for employee records, item inventory and your tickets',
            'IT' => 'A user guide for handling tickets, reports, guides and ratings',
            'AOM' => 'A user guide for the tickets, employees and assets of your branches',
            'OM' => 'A user guide for operations tickets, employees, assets and AOM branches',
            'HOM' => 'A user guide for all operations tickets, employees, branches and announcements',
            'ADMIN' => 'A user guide for accounts, tickets, assets, system settings and reports',
        ];
        return $texts[$role];
    }

    private static function whatLead(string $role): string
    {
        return $role === 'ADMIN'
            ? 'You manage the whole TMS: who can sign in, how tickets are assigned, the assets, and what everyone sees.'
            : 'One place to report IT concerns and follow them until they are fixed. Here is what your account can do.';
    }

    private static function whatCards(string $role): array
    {
        $file = ['fa-plus', 'File tickets', 'Report any IT concern with photos, a category and a priority.', true];
        $track = ['fa-tasks', 'Track progress', 'Follow the status, history and chat of each ticket.', false];
        $close = ['fa-file-signature', 'Sign and rate', 'Sign the technical report and rate the IT service.', true];
        $assets = ['fa-laptop', 'Your assets', 'See the equipment and items assigned to you.', false];
        $cards = [
            'EMPLOYEE' => [$file, $track, $close, $assets],
            'HEAD' => [$file, ['fa-building', 'Department tickets', 'See and follow the tickets of everyone in your department.', false],
                ['fa-bullhorn', 'Announcements', 'Post announcements for your department (Admin approves them).', true], $assets],
            'HR' => [['fa-users', 'Employee records', 'Search employees, see their details and accountability forms.', false],
                ['fa-tshirt', 'Item inventory', 'Add items, issue them to employees and keep the stock right.', true],
                ['fa-exclamation-triangle', 'Lost / damaged', 'Check and answer the lost or damaged reports.', false], $file],
            'IT' => [['fa-inbox', 'Assigned tickets', 'Work on the tickets assigned to you and update them.', true],
                ['fa-comments', 'Chat and guides', 'Chat with requesters and send troubleshooting guides.', false],
                ['fa-file-signature', 'Technical reports', 'Make, sign and collect the technical reports.', true],
                ['fa-book-medical', 'Manuals', 'Write the troubleshooting guides everyone uses.', false]],
            'AOM' => [['fa-store', 'Your branches', 'Tickets, employees and assets of the branches assigned to you.', false],
                ['fa-user-plus', 'File for employees', 'File a ticket for an employee of your branches, or your own.', true], $track,
                ['fa-exchange-alt', 'Bulk transfer', 'Move all tickets of one employee to another.', false]],
            'OM' => [['fa-store', 'Operations', 'Tickets, employees and assets of operations.', false],
                ['fa-user-plus', 'File for employees', 'File a ticket for an employee, or your own.', true],
                ['fa-map-marked-alt', 'AOM branches', 'See which branches each AOM handles.', false], $track],
            'HOM' => [['fa-store', 'All operations', 'Tickets, employees and assets of every branch.', false],
                ['fa-user-plus', 'File for employees', 'File a ticket for any operations employee, or your own.', true],
                ['fa-map-marked-alt', 'Branches and transfers', 'Assign branches to AOMs and transfer employees and tickets.', false],
                ['fa-bullhorn', 'Announcements', 'Post announcements (Admin approves them).', true]],
            'ADMIN' => [['fa-users-cog', 'Users', 'Accounts, roles, passwords and departments.', false],
                ['fa-ticket-alt', 'Tickets', 'All tickets, assignment rules and IT availability.', true],
                ['fa-boxes', 'Assets', 'The assets directory, defective items and replacements.', false],
                ['fa-cogs', 'System', 'Announcements, IT manuals, themes, chat, audit trail and reports.', true]],
        ];
        return $cards[$role];
    }

    private static function signIn(string $role): array
    {
        return self::steps('Sign in to the TMS', 'Use any browser on your computer or phone.', [
            ['Open the website', 'Go to tms.storagemart.com.ph'],
            ['Username', 'Type your company email address.'],
            ['Password', 'The first time, it is your SURNAME in capital letters (e.g. LABIDES), unless the Admin gave you another one.'],
            ['Click Sign in', 'You land on your Dashboard. After 3 wrong passwords the account is locked for 15 minutes.'],
        ], ['url' => '/login', 'login' => true], null,
            'Forgot your password? Click "Forgot password?" under Sign in and enter your email. IT will check that it is you and give you a temporary password.');
    }

    private static function header(string $role): array
    {
        $steps = [
            ['☰ Menu', 'Shows or hides the left menu. On a phone, the menu opens from here.'],
            ['Bell', 'Notifications: ticket updates, new chat messages and replies. Click one to open it.'],
            ['Moon', 'Dark mode on or off (easier on the eyes at night).'],
            ['Megaphone', 'Announcements and birthdays. The red number shows what is new.'],
        ];
        $top = ['menu' => 1, 'bell' => 2, 'moon' => 3, 'bullhorn' => 4];
        if ($role === 'ADMIN') {
            $steps[] = ['Chat', 'Start a chat with any user about a concern (see "Chat with users").'];
        } else {
            $steps[] = ['Chat', 'Appears when the Admin sends you a chat request.'];
        }
        $top['chat'] = 5;
        $steps[] = ['?', 'Opens this guide again at any time.'];
        $top['question'] = 6;
        return self::steps('The top bar', 'The same buttons are on every page.', $steps,
            ['url' => self::ROLES[$role]['home'], 'menu' => 'Dashboard', 'title' => 'Dashboard', 'top' => $top, 'blocks' => [['cards', ['', '', '', '']]]], null,
            'During holidays and events a magic wand appears: it plays the season\'s screen effects. It rests for a few seconds after each click.');
    }

    private static function passwordSlide(string $role): array
    {
        return self::steps('Your password, profile and logging out', 'Your name card is at the top of the left menu.', [
            ['Change password', 'Click "Change password" under your name right after your first sign-in. Use at least 8 characters with an uppercase letter, a lowercase letter, a number and a symbol.'],
            ['Your profile', 'Click your name to see your account and personal details (birthday, date hired, phone).'],
            ['Logout', 'Click Logout, then Sign out. Always log out on a shared computer.'],
        ], ['url' => '/change-password', 'menu' => 'Dashboard', 'title' => 'Change password', 'card' => true,
            'blocks' => [['form', [['Current password', 1], 'New password', 'Confirm new password']]]], ['/change-password', 'Change my password']);
    }

    private static function dashboard(string $role, string $url, string $title, array $steps, array $block1, array $block2): array
    {
        return self::steps('Your dashboard at a glance', 'This is the first page after you sign in.', $steps,
            ['url' => $url, 'menu' => 'Dashboard', 'title' => $title, 'blocks' => [$block1, $block2]], [$url, 'Open my dashboard']);
    }

    /** Filing a ticket: the form, priority and how to write it. */
    private static function ticketSlides(string $role, string $url, string $menu, string $submit): array
    {
        $out = [];
        $out[] = self::steps('File a new ticket', 'One concern per ticket. It takes about a minute.', [
            ['Open the form', 'Click "' . ($menu === 'Tickets' ? 'Create New Ticket' : 'Create Ticket') . '" in the menu or on the ticket list.'],
            ['Check your details', 'Your employee ID, name, department and branch are filled in for you.'],
            ['Subject and Description', 'Say which device, where, and what happens (see the example on the next slides).'],
            ['Photos (optional)', 'Add up to 4 photos of the screen, error or device. On a phone, "Take a photo" opens the camera.'],
            ['Category and Priority', 'Pick what the problem is about and how urgent it is.'],
            ['Click ' . $submit, 'Check the Live Preview first. A troubleshooting guide is sent in the ticket chat right away.'],
        ], ['url' => $url, 'menu' => $menu, 'title' => 'New Support Ticket',
            'blocks' => [['form', ['Employee ID · Name · Department · Branch', ['Subject *', 3], 'Description *', ['Photos of the problem', 4], ['Category · Priority', 5]], 2], ['buttons', [[$submit, 6], 'Cancel']]]], [$url, 'Open the ticket form']);
        $out[] = ['type' => 'priority'];
        $out[] = ['type' => 'write'];
        return $out;
    }

    private static function trackTickets(string $role, string $url, string $menu): array
    {
        return self::steps('Track your tickets', 'Every ticket you filed is listed here.', [
            ['Open ' . $menu, 'Click ' . $menu . ' in the menu.'],
            ['Filter and search', 'Choose a Status or Priority, or type a ticket number. Reset clears the filters.'],
            ['Open a ticket', 'Click the eye button to see the details, the history and the chat.'],
        ], ['url' => $url, 'menu' => $menu, 'title' => $menu,
            'blocks' => [['filters', [['Status', 2], 'Priority', 'Search']], ['table', ['Ticket #', 'Subject', 'Priority', 'Status', 'Action'], 3]]], [$url, 'Open my tickets']);
    }

    private static function insideTicket(string $role): array
    {
        $it = $role === 'IT';
        return self::steps('Inside a ticket', 'Everything about one concern is on one page.', [
            ['Details and history', 'Employee, branch, priority, status and every update in order.'],
            ['Comments and chat', 'Write to ' . ($it ? 'the requester' : 'IT') . ', attach photos or files, or press Ctrl+V to paste a screenshot. The chat bubble at the bottom right of every page has your open tickets.'],
            ['Troubleshooting guide', 'The best guide for the problem is sent in the chat. Try its steps; many problems are fixed in minutes.'],
            ['Need help from IT personnel', $it ? 'When the requester presses it, you get a notification: call or visit them.' : 'If the guide did not help, press this button in the chat so the IT person helps you directly.'],
            ['Cancel Ticket', $role === 'ADMIN' ? 'The Admin can cancel any open ticket, with a reason.' : 'Only before IT is assigned, with a reason. After that, ask IT in the chat (only the Admin can cancel it).'],
        ], ['url' => self::ROLES[$role]['area'] . '/tickets/view?id=…', 'menu' => $it ? 'Ticket' : null, 'title' => 'Ticket Details',
            'buttons' => [['Cancel Ticket', 5]], 'blocks' => [['table', ['Status', 'Priority', 'Filed', 'Assigned to'], 1], ['chat', 2], ['buttons', [['Open guide', 3], ['Need help from IT personnel', 4]]]]]);
    }

    private static function closeLoop(string $role): array
    {
        return self::steps('Close the loop when it is resolved', 'You get a notification when IT marks your ticket Resolved.', [
            ['Open the resolved ticket', 'From the notification or your ticket list.'],
            ['Sign the report', 'Click "Sign report" and sign on the screen, or download the report (PDF / Word), sign it on paper and upload it under Technical Report.'],
            ['Rate the service', 'Click "Rate Ticket" and give 1 to 5 stars with a comment. It helps IT improve.'],
        ], ['url' => self::ROLES[$role]['area'] . '/tickets/view?id=…', 'title' => 'Ticket Details',
            'buttons' => [['Sign report', 2]], 'blocks' => [['table', ['Status: Resolved', 'Technical report', 'Signed', ''], 1], ['buttons', ['PDF', 'Word', 'Upload Report', ['Rate Ticket', 3]]]]]);
    }

    private static function assets(string $role, string $url, string $menu, string $title): array
    {
        return self::steps('Your assets', 'The equipment assigned to you by IT / the Admin.', [
            ['Open ' . $menu, 'Click ' . $menu . ' in the menu.'],
            ['Check your items', 'Each item shows its model, serial number and asset number.'],
            ['Report a problem', 'Open an item and file a ticket from there, so IT knows which device it is.'],
        ], ['url' => $url, 'menu' => $menu, 'title' => $title, 'blocks' => [['cards', ['Assigned', 'Laptops', 'Desktops', 'Others'], 2], ['table', ['Item', 'Serial', 'Asset #', 'Action'], 3]]], [$url, 'Open my assets']);
    }

    private static function issuedItems(string $role, string $url): array
    {
        return self::steps('My Issued Items', 'Uniforms and other items HR issued to you.', [
            ['Open My Issued Items', 'Click My Issued Items in the menu.'],
            ['Check the list', 'Item, size, quantity and date issued.'],
            ['Confirm received', 'When HR gives you an item, click "Confirm received" so HR knows you have it.'],
            ['Report Lost / Damaged', 'Click it on the item, say what happened, and send. HR checks it and answers.'],
        ], ['url' => $url, 'menu' => 'My Issued Items', 'title' => 'My Issued Items',
            'blocks' => [['table', ['Item', 'Size', 'Qty', 'Date Issued', 'Received'], 2], ['buttons', [['Confirm received', 3], ['Report Lost / Damaged', 4]]]]], [$url, 'Open My Issued Items']);
    }

    private static function announcementPost(string $role, string $url, string $who): array
    {
        return self::steps('Post an announcement', 'For your ' . ($role === 'HEAD' ? 'department' : 'teams') . '. The Admin checks it first.', [
            ['Title and Message', 'Keep it short and clear, e.g. "File a ticket before any IT work".'],
            ['Send to', 'Choose who sees it' . ($role === 'HEAD' ? ' (your department)' : '') . '.'],
            ['Show from / Until', 'Leave Show from empty to start now, and Until empty for no end.'],
            ['Important', 'Tick it for urgent news: it shows in red, first, and stays on the screen for 5 seconds before it can be closed.'],
            ['Post announcement', 'It is sent to the Admin for approval. Once approved, it pops up once for everyone it is sent to.'],
        ], ['url' => $url, 'menu' => 'Announcements', 'title' => 'Announcements',
            'blocks' => [['form', [['Title *', 1], 'Message *', ['Send to', 2], ['Show from · Until', 3], ['Important', 4]]], ['buttons', [['Post announcement', 5]]]]], [$url, 'Open Announcements']);
    }

    private static function announcementsSlide(string $role): array
    {
        return self::steps('Announcements', 'News from the Admin, your head or operations.', [
            ['They pop up first', 'After you sign in, new announcements open one by one. Important ones (red) can be closed after 5 seconds.'],
            ['Got it / Remind me later', '"Got it" marks it as read. "Remind me later" shows it again next time you sign in.'],
            ['Read them again', 'Click the megaphone in the top bar. The red number shows the new ones.'],
        ], ['url' => self::ROLES[$role]['home'], 'menu' => 'Dashboard', 'title' => 'Dashboard', 'top' => ['bullhorn' => 3],
            'blocks' => [['note', 'IMPORTANT ANNOUNCEMENT · Office closed on Monday', 1], ['buttons', ['Remind me later', ['Got it', 2]]]]]);
    }

    private static function birthdaysSlide(string $role): array
    {
        return self::steps('Birthdays', 'Birthdays are part of the announcements.', [
            ['Birthday today', 'When a colleague has a birthday, a "Birthday today" card shows after the announcements.'],
            ['Greet', 'Click Greet, change the message if you like, and click Send. They see it when they sign in.'],
            ['Coming up', 'The megaphone panel also lists the birthdays in the next 3 days with a countdown.'],
            ['Your birthday', 'On your day you get a greeting with your colleagues\' wishes. Only the day is shown, never the year.'],
        ], ['url' => self::ROLES[$role]['home'], 'menu' => 'Dashboard', 'title' => 'Dashboard', 'top' => ['bullhorn' => 3],
            'blocks' => [['note', '🎂 Birthday today · Juan Dela Cruz · Operations · Eran', 1], ['buttons', [['Greet', 2]]], ['list', ['Coming up: Ana Reyes · Tomorrow · 23:59:10']]]]);
    }

    private static function chatRequests(): array
    {
        return self::steps('Chat requests from the Admin', 'The Admin may ask to chat with you about a concern.', [
            ['A request pops up', 'It shows the concern and how much time you have to answer.'],
            ['Accept or Decline', 'Accept opens the chat. To decline, you can write a reason (e.g. "In a meeting until 3 PM").'],
            ['Urgent chats', 'An urgent chat opens right away, without accept or decline.'],
            ['End', 'Either side can end the chat. Past chats are under the chat icon in the top bar.'],
        ], ['url' => '/', 'title' => 'Dashboard', 'top' => ['chat' => 4],
            'blocks' => [['note', 'Admin wants to chat with you · Please answer within 4:59', 1], ['buttons', [['Decline', 2], ['Accept and chat', 2]]]]]);
    }

    private static function manualsLibrary(string $role): array
    {
        return self::steps('IT manuals library', 'Step-by-step slides for common IT problems, and this guide.', [
            ['Open the library', 'Open tms.storagemart.com.ph/manuals, or the link sent in a ticket chat.'],
            ['Find a guide', 'Pick a group (Printer, Computer, Network, CCTV…) or type in Search.'],
            ['View or download', 'View slides opens it on the screen (Full screen works too). PowerPoint downloads it.'],
            ['TMS user guides', 'At the top of the library: the TMS guide for your role.' . (in_array($role, ['IT', 'ADMIN'], true) ? ' IT and Admin see every role\'s guide.' : '')],
        ], ['url' => '/manuals', 'title' => 'IT Manuals', 'blocks' => [['buttons', ['All', ['Printer Issues', 2], 'Computer', 'Network', 'CCTV'], 0], ['tiles', ['Printer Paper Jam', 'No Internet', 'PC Won\'t Turn On', 'Outlook Won\'t Open'], 3]]], ['/manuals', 'Open the library']);
    }

    private static function manualsEditor(string $role, string $url): array
    {
        return self::steps('Write a troubleshooting guide', 'Guides become slides and a PowerPoint in the IT manual design.', [
            ['Open the guide maker', 'Click ' . ($role === 'IT' ? 'Troubleshooting Guides' : 'System > IT Manuals') . ', then "Create a guide". "How to create a guide" walks you through it.'],
            ['Follow the 4 steps', 'Basics (title, group, who can open it), The problem, The fix (steps with photos), Finish (categories and keywords).'],
            ['Watch the preview', 'The slides on the right change as you type. Click a slide to jump to its box.'],
            ['Save guide', 'It is sent in the chat of matching tickets. Use "Test: which guide is sent?" to check.'],
        ], ['url' => $url, 'menu' => $role === 'IT' ? 'Troubleshooting Guides' : 'System', 'title' => 'Troubleshooting Manuals',
            'buttons' => [['Create a guide', 1], 'How to create a guide'], 'blocks' => [['buttons', ['1 Basics', '2 The problem', '3 The fix', '4 Finish'], 2], ['tiles', ['Cover', 'Main Issue', 'STEP 1', 'Thank you'], 3], ['buttons', [['Save guide', 4]]]]], [$url, 'Open the guide maker']);
    }

    // ------------------------------------------------------------------ operations (AOM / OM / HOM)

    private static function operationsSlides(string $role, string $a): array
    {
        $isHom = $role === 'HOM';
        $s = [];
        $s[] = self::dashboard($role, $a . '/dashboard', 'Dashboard', [
            ['Quick buttons', 'My Ticket, Employee Ticket, Employees and All Tickets.'],
            [$role === 'AOM' ? 'Assigned Branches' : 'Ticket Breakdown', $role === 'AOM' ? 'The branches you handle and their ticket numbers.' : 'Tickets by status for operations.'],
            ['Recent Tickets', 'The newest tickets. Click View (or Details) to open one.'],
        ], ['buttons', ['My Ticket', 'Employee Ticket', 'View Employees', 'All Tickets'], 1], ['table', ['Ticket #', 'Employee', 'Status', 'Action'], 3]);
        $s[] = ['type' => 'flow'];
        $s[] = ['type' => 'statuses'];
        $s[] = self::steps('File a ticket for an employee', 'For staff who cannot file it themselves.', [
            ['Open Employee Ticket', 'Click Employee Ticket in the menu.'],
            [$isHom ? 'Employee' : 'Branch, then Employee', $isHom ? 'Choose the operations employee; the branch is filled in.' : 'Choose the branch first (only your branches), then the employee.'],
            ['Subject, Description, Photos', 'Write which device, where and what happens. Add photos if you can.'],
            ['Category and Priority', 'Then click Create Ticket. The employee can follow it in their own account.'],
        ], ['url' => $a . '/tickets/create/employee', 'menu' => 'Employee Ticket', 'title' => 'Create Employee Ticket',
            'blocks' => [['form', [['Branch *', 2], ['Employee *', 2], ['Subject *', 3], 'Description *', ['Category · Priority', 4]]], ['buttons', [['Create Ticket', 4], 'Cancel']]]], [$a . '/tickets/create/employee', 'File an employee ticket']);
        $s[] = self::steps('File your own ticket', 'For a problem with your own device or account.', [
            ['Open My Ticket', 'Click My Ticket in the menu. Your details are filled in.'],
            ['Describe it', 'Subject, Description, photos, Category and Priority.'],
            ['Create My Ticket', 'You follow it under Tickets like any other.'],
        ], ['url' => $a . '/tickets/create/my', 'menu' => 'My Ticket', 'title' => 'Create My Ticket',
            'blocks' => [['form', ['Employee · Department · Branch', ['Subject *', 2], 'Description *', 'Category · Priority']], ['buttons', [['Create My Ticket', 3]]]]], [$a . '/tickets/create/my', 'File my ticket']);
        $s[] = ['type' => 'priority'];
        $s[] = ['type' => 'write'];
        $s[] = self::steps('Branch tickets and Bulk Transfer', 'Every ticket of ' . ($isHom ? 'operations' : 'your branches') . '.', [
            ['Filter', 'Choose Status, Branch or Priority, or search a ticket number.'],
            ['Open a ticket', 'Click the eye button for details, history and chat.'],
            ['Bulk Transfer', 'Moves all tickets of one employee in a branch to another employee (for example when staff move). Choose the branch, both employees, and a remark.'],
        ], ['url' => $a . '/tickets', 'menu' => 'Tickets', 'title' => 'Tickets', 'buttons' => [['Bulk Transfer', 3], 'My Ticket', 'Employee Ticket'],
            'blocks' => [['filters', [['Status', 1], 'Branch', 'Priority']], ['table', ['Ticket #', 'Employee', 'Branch', 'Status', 'Actions'], 2]]], [$a . '/tickets', 'Open Tickets']);
        $s[] = self::insideTicket($role);
        $s[] = self::closeLoop($role);
        $s[] = self::steps('Employees', $isHom ? 'Operations and HOM staff of every branch.' : 'The operations staff of your branches.', [
            ['Filter by branch', 'Choose a branch, or Clear Filters.'],
            ['View', 'See the employee\'s details, tickets and assets.'],
            $isHom ? ['Transfer', 'Moves an employee to another branch.'] : ['Status', 'Shows who is active.'],
        ], ['url' => $a . '/employees', 'menu' => 'Employees', 'title' => 'Operations Employees',
            'blocks' => [['filters', [['Branch', 1]]], ['table', ['Employee', 'Position', 'Branch', $isHom ? 'Assigned AOM' : 'Status', 'Actions'], 2], ['buttons', [$isHom ? ['Transfer', 3] : ['View', 2]]]]], [$a . '/employees', 'Open Employees']);
        $s[] = self::steps('Assets', 'Your own equipment and the equipment of your employees.', [
            ['My Assets', 'The items assigned to you.'],
            ['Employee Assets', 'Choose a branch to see the items of its employees.'],
            ['Report a problem', 'File an Employee Ticket for the person who has the item.'],
        ], ['url' => $a . '/assets', 'menu' => 'Assets', 'title' => 'Operations Assets',
            'blocks' => [['table', ['My Assets', 'Serial', 'Asset #'], 1], ['filters', [['Branch', 2]]], ['table', ['Employee', 'Item', 'Serial'], 2]]], [$a . '/assets', 'Open Assets']);
        if ($role !== 'AOM') {
            $s[] = self::steps('AOM branch assignments', 'Which branches each Area Operations Manager handles.', [
                ['Open AOM Branches', 'Click AOM Branches in the menu.'],
                ['See the list', 'Each AOM with their email and assigned branches.'],
                ['Assign Branches', 'Click it on an AOM, tick their branches and save. Their dashboard and tickets follow.'],
            ], ['url' => $a . '/aom-branches', 'menu' => 'AOM Branches', 'title' => 'AOM Branch Assignments',
                'blocks' => [['table', ['AOM', 'Email', 'Assigned Branches', 'Actions'], 2], ['buttons', [['Assign Branches', 3]]]]], [$a . '/aom-branches', 'Open AOM Branches']);
        }
        if ($isHom) {
            $s[] = self::announcementPost($role, $a . '/announcements', 'Head of Operations');
        }
        $s[] = self::issuedItems($role, $a . '/items');
        return $s;
    }

    // ------------------------------------------------------------------ Admin

    private static function adminSlides(): array
    {
        $s = [];
        $s[] = self::dashboard('ADMIN', '/admin', 'Admin Dashboard', [
            ['Overview cards', 'Users, tickets, open, in progress, SLA compliance and assets. Click a card to open the list.'],
            ['Charts', 'Tickets by status, category, branch and priority, the top 5 issues and resolution time (SLA).'],
            ['IT Personnel Workload', 'How many tickets each IT person has.'],
        ], ['cards', ['Total Users', 'Tickets', 'Open', 'SLA'], 1], ['buttons', ['View Tickets', 'Pending', 'Audit Trail'], 1]);
        $s[] = ['type' => 'flow'];
        $s[] = ['type' => 'statuses'];
        $s[] = self::steps('Users and employees', 'Who can sign in, and with which role.', [
            ['Filter', 'By Role, Department, Branch or Status (Active, Inactive, No login).'],
            ['Add User', 'Username (company email), role, employee details, department and branch, and a simple first password (for example the SURNAME in capitals).'],
            ['Edit user', 'Change the role, status or details. To reset a password, type a new temporary one and tell the person to change it after signing in.'],
            ['Unlock login now', 'After 3 wrong passwords an account is locked for 15 minutes. The lock button opens it right away.'],
            ['Delete or deactivate', 'Deactivate someone who left; delete only accounts made by mistake.'],
        ], ['url' => '/admin/account', 'menu' => 'Users', 'title' => 'Users & Employees', 'buttons' => [['Add User', 2]],
            'blocks' => [['filters', [['Role', 1], 'Department', 'Branch', 'Status']], ['table', ['Name', 'Role', 'Branch', 'Status', 'Actions'], 3], ['buttons', [['Edit', 3], ['Unlock', 4], ['Deactivate', 5]]]]], ['/admin/account', 'Open Users']);
        $s[] = self::steps('Departments', 'The departments used in every dropdown.', [
            ['Add Department', 'Code (saved on each employee) and Display Label (what people see).'],
            ['Rename', 'Renaming a code moves its employees automatically.'],
            ['Check the count', 'The list shows how many employees each department has.'],
        ], ['url' => '/admin/department', 'menu' => 'Users', 'title' => 'Manage Departments',
            'blocks' => [['form', [['Code *', 1], 'Display Label *']], ['buttons', [['Add Department', 1]]], ['table', ['Code', 'Display Label', 'Employees'], 3]]], ['/admin/department', 'Open Departments']);
        $s[] = self::steps('All tickets', 'Every ticket in the company.', [
            ['Filter', 'By branch, priority and status. The status cards open their list.'],
            ['Add Ticket', 'File a ticket for anyone.'],
            ['Update Assignment', 'On a ticket, assign it or move it to another IT person.'],
            ['Download record', 'PDF or Word record of a ticket, and the Ticket Report for a period.'],
        ], ['url' => '/admin/tickets', 'menu' => 'Ticket', 'title' => 'All Tickets', 'buttons' => [['Add Ticket', 2], 'Ticket Report'],
            'blocks' => [['filters', [['Branch', 1], 'Priority', 'Status']], ['table', ['Ticket', 'Employee', 'Priority', 'Assigned To', 'Actions'], 3], ['buttons', [['Update Assignment', 3], ['PDF / Word', 4]]]]], ['/admin/tickets', 'Open All Tickets']);
        $s[] = self::steps('Ticket auto-assignment', 'New tickets go to the right IT person by themselves.', [
            ['Rules', 'Turn "Assign new tickets automatically" on, and set after how long an unopened ticket moves to the next IT person.'],
            ['Zones', 'Choose the IT person of each zone (A, B…). Add a zone when needed.'],
            ['Branch Zones', 'Put each branch in a zone. Its tickets go to that zone\'s IT person.'],
            ['Saturday duty', 'Choose the IT person for each month\'s Saturdays (the rotation). On Saturdays every new ticket goes to them. "Change one Saturday" is for swaps.'],
            ['IT Staff Availability', 'Mark IT staff as Away: no new tickets, and unopened ones move to someone else. Then Save Settings.'],
        ], ['url' => '/admin/ticket-assignment', 'menu' => 'System', 'title' => 'Ticket Auto-Assignment',
            'blocks' => [['switch', 'Assign new tickets automatically', 1], ['table', ['Month', 'Saturdays', 'IT person on duty'], 4], ['form', [['Zone A IT person', 2], 'Zone B IT person']], ['table', ['Branch', 'Zone', 'Tickets go to'], 3], ['buttons', [['Away', 5], ['Save Settings', 5]]]]], ['/admin/ticket-assignment', 'Open Auto-Assignment']);
        $s[] = self::steps('Assets directory', 'Every company asset, by model / group.', [
            ['Browse', 'Filter by category. Each group shows total, assigned, unassigned, defective and for replacement.'],
            ['Add Item', 'Asset group, general info, serial number, year purchased and branch.'],
            ['Add Model / Group, Category, Branch', 'Create these first so new items can use them.'],
            ['Defective Items and replacements', 'See the defective items by month (Download Excel) and items 5+ years old (For replacement).'],
            ['Import from Excel', 'Add many items at once.'],
        ], ['url' => '/admin/assets', 'menu' => 'Assets Directory', 'title' => 'Assets Directory',
            'buttons' => [['Add Item', 2], ['Add Model / Group', 3], 'Add Category', ['Defective Items', 4]],
            'blocks' => [['filters', [['Category', 1]]], ['table', ['Model', 'Total', 'Assigned', 'Defective', 'For Replacement'], 1], ['buttons', [['Import from Excel', 5]]]]], ['/admin/assets', 'Open Assets Directory']);
        $s[] = self::steps('Announcements and approvals', 'What everyone sees after signing in.', [
            ['Post', 'Title, Message, Send to (everyone or one department), dates, and Important for red urgent news.'],
            ['Approve', 'Announcements from Department Heads and the HOM wait for you. Click "Approve & post" (or reject).'],
            ['Edit or delete', 'Use the pencil or the trash button in the Posted list.'],
        ], ['url' => '/admin/announcements', 'menu' => 'System', 'title' => 'Announcements',
            'blocks' => [['form', [['Title *', 1], 'Message *', 'Send to', 'Important']], ['list', ['Waiting for approval · From Department Head', 'Approve & post']], ['table', ['Posted', 'Audience', 'Until'], 3]]], ['/admin/announcements', 'Open Announcements']);
        $s[] = self::manualsEditor('ADMIN', '/admin/manuals');
        $s[] = self::steps('Themes and events', 'A seasonal look with screen effects for holidays.', [
            ['Theme list', 'Each theme shows its effects. Press ▶ to test one effect, Preview to see the whole look (only you see it).'],
            ['Add a theme', 'Name, emoji, colors, greeting and effects. "How to add a theme" shows every step.'],
            ['Schedule', 'Set the release date and time and the end. Everyone sees it during that time.'],
        ], ['url' => '/admin/themes', 'menu' => 'System', 'title' => 'Seasonal Themes', 'buttons' => [['Add a theme', 2], 'How to add a theme'],
            'blocks' => [['tiles', ['Halloween', 'Christmas', 'New Year', 'Valentine\'s'], 1], ['buttons', ['Preview', ['Schedule', 3]]]]], ['/admin/themes', 'Open Themes']);
        $s[] = self::steps('Chat with users', 'Ask anyone about a concern, with a time to answer.', [
            ['New chat', 'Click the chat icon in the top bar, then New chat.'],
            ['Person and concern', 'Search a name, department or branch and pick the person. Write the concern.'],
            ['Time or Urgent', 'Choose how many minutes they have to answer, or tick Urgent to open the chat right away.'],
            ['Send chat request', 'You see when they accept or decline (with their reason). End closes the chat.'],
        ], ['url' => '/admin', 'menu' => 'Dashboard', 'title' => 'Admin Dashboard', 'top' => ['chat' => 1],
            'blocks' => [['form', [['Person', 2], 'Concern', ['5 min to answer · Urgent', 3]]], ['buttons', [['Send chat request', 4]]]]]);
        $s[] = self::steps('Audit trail and reports', 'Who did what, and how IT is doing.', [
            ['Audit Trail', 'Every change with its Log No. Click a row to see the full history of that record.'],
            ['Ratings Report', 'Ratings of each IT person, with the details.'],
            ['Ticket Report', 'Tickets for a period, to view or download.'],
        ], ['url' => '/admin/audit-trail', 'menu' => 'Audit Trail', 'title' => 'Audit Trail',
            'blocks' => [['table', ['Log No. / Record', 'Action', 'User', 'Date'], 1], ['buttons', [['Ratings Report', 2], ['Ticket Report', 3]]]]], ['/admin/audit-trail', 'Open the Audit Trail']);
        $s[] = self::steps('Full-access Admin (GM)', 'Admins marked as full access can open every role\'s pages.', [
            ['Other areas', 'The Admin menu shows HR, IT, Operations and Department Head pages.'],
            ['Your role stays Admin', 'You work in that area as its role for that page only, and your own name is on everything you do.'],
        ], ['url' => '/admin', 'menu' => 'Dashboard', 'title' => 'Admin Dashboard', 'blocks' => [['list', ['HR area', 'IT area', 'Operations area', 'Department Head area'], 1]]]);
        $s[] = self::issuedItems('ADMIN', '/admin/items');
        return $s;
    }

    private static function recapItems(string $role): array
    {
        $common = [['Sign in', 'With your company email; change your password the first time.']];
        $byRole = [
            'EMPLOYEE' => [['File it right', 'Clear subject and description, photos, the right category, an honest priority.'],
                ['Try the guide', 'Follow the troubleshooting guide in the chat; press "Need help" if it does not work.'],
                ['Close the loop', 'Sign the technical report and rate the service.']],
            'HEAD' => [['Your department', 'Follow its tickets and employees.'], ['Announce', 'Post department announcements; the Admin approves them.'],
                ['Close the loop', 'Sign the technical report and rate the service.']],
            'HR' => [['Keep records right', 'Employees, items issued and returned.'], ['Answer reports', 'Check the lost / damaged reports.'],
                ['Tickets', 'File and follow your IT concerns.']],
            'IT' => [['Be available', 'Use "I\'m away" when absent.'], ['Help fast', 'Open new tickets quickly, chat, send guides.'],
                ['Finish', 'Mark as Done, sign the report, keep the guides up to date.']],
            'AOM' => [['Your branches', 'Follow their tickets, employees and assets.'], ['File for staff', 'Employee Ticket for those who cannot file.'],
                ['Close the loop', 'Make sure reports are signed and rated.']],
            'OM' => [['Operations', 'Follow tickets, employees and assets.'], ['File for staff', 'Employee Ticket for those who cannot file.'],
                ['Close the loop', 'Make sure reports are signed and rated.']],
            'HOM' => [['All branches', 'Tickets, employees, transfers and AOM branches.'], ['Announce', 'Post announcements; the Admin approves them.'],
                ['Close the loop', 'Make sure reports are signed and rated.']],
            'ADMIN' => [['People', 'Accounts, roles and departments up to date.'], ['Tickets', 'Assignment rules and IT availability.'],
                ['Communicate', 'Announcements, chat, themes; check the audit trail and reports.']],
        ];
        return array_merge($common, $byRole[$role]);
    }
}
