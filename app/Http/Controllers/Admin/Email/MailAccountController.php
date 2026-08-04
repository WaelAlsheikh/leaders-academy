<?php

namespace App\Http\Controllers\Admin\Email;

use App\Domain\Email\Models\MailAccount;
use App\Domain\Email\Models\MailAlias;
use App\Domain\Email\Models\MailAuditLog;
use App\Domain\Email\Models\MailDistributionList;
use App\Domain\Email\Services\AliasService;
use App\Domain\Email\Services\DistributionListService;
use App\Domain\Email\Services\MailboxProvisioningService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MailAccountController extends Controller
{
    public function __construct(
        private readonly MailboxProvisioningService $provisioning,
        private readonly AliasService $aliases,
        private readonly DistributionListService $lists,
    ) {}

    public function index(Request $request)
    {
        $accounts = MailAccount::query()
            ->with(['domain', 'mailable', 'mailbox'])
            ->when($request->identity_type, fn ($q) => $q->where('identity_type', $request->identity_type))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->q, function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('institutional_email', 'like', $term)
                        ->orWhere('local_part', 'like', $term);
                });
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $health = $this->provisioning->healthCheck();

        return view('admin.email.accounts.index', array_merge(
            compact('accounts', 'health'),
            $this->portalViewData($request)
        ));
    }

    public function show(Request $request, MailAccount $account)
    {
        $account->load(['domain', 'mailbox', 'aliases', 'forwards', 'mailable']);
        $logs = MailAuditLog::query()
            ->where('mail_account_id', $account->id)
            ->latest('id')
            ->limit(50)
            ->get();

        return view('admin.email.accounts.show', array_merge(
            compact('account', 'logs'),
            $this->portalViewData($request)
        ));
    }

    public function disable(MailAccount $account)
    {
        $this->provisioning->disable($account);

        return back()->with('success', 'تم تعطيل الصندوق.');
    }

    public function enable(MailAccount $account)
    {
        $this->provisioning->enable($account);

        return back()->with('success', 'تم تفعيل الصندوق.');
    }

    public function resetPassword(MailAccount $account)
    {
        $password = $this->provisioning->resetPassword($account);

        return back()->with('success', 'تم إعادة تعيين كلمة المرور: '.$password);
    }

    public function updateQuota(Request $request, MailAccount $account)
    {
        $data = $request->validate(['quota_mb' => 'required|integer|min:100|max:102400']);
        $this->provisioning->changeQuota($account, (int) $data['quota_mb']);

        return back()->with('success', 'تم تحديث الحصة.');
    }

    public function storeAlias(Request $request, MailAccount $account)
    {
        $data = $request->validate([
            'source_email' => 'required|email|max:255',
            'type' => 'nullable|in:user,functional,legacy',
        ]);

        $this->aliases->createForAccount($account, $data['source_email'], $data['type'] ?? 'user');

        return back()->with('success', 'تم إنشاء الـ alias.');
    }

    public function destroyAlias(MailAlias $alias)
    {
        $this->aliases->remove($alias);

        return back()->with('success', 'تم حذف الـ alias.');
    }

    public function lists(Request $request)
    {
        $lists = MailDistributionList::query()->withCount('members')->orderBy('address')->paginate(25);

        return view('admin.email.lists.index', array_merge(
            compact('lists'),
            $this->portalViewData($request)
        ));
    }

    public function storeList(Request $request)
    {
        $data = $request->validate([
            'address' => 'required|email|max:255',
            'name' => 'required|string|max:255',
            'identity_type' => 'nullable|in:student,doctor,employee,admin',
            'college_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
        ]);

        $rule = array_filter([
            'identity_type' => $data['identity_type'] ?? null,
            'college_id' => $data['college_id'] ?? null,
            'section_id' => $data['section_id'] ?? null,
        ]);
        $list = $this->lists->create($data['address'], $data['name'], $rule ?: null, (bool) $rule);
        if ($rule) {
            $this->lists->sync($list);
        }

        return back()->with('success', 'تم إنشاء القائمة.');
    }

    public function syncList(MailDistributionList $list)
    {
        $count = $this->lists->sync($list);

        return back()->with('success', "تمت مزامنة {$count} عضو.");
    }

    public function logs(Request $request)
    {
        $logs = MailAuditLog::query()->with('account')->latest('id')->paginate(50);

        return view('admin.email.logs.index', array_merge(
            compact('logs'),
            $this->portalViewData($request)
        ));
    }

    private function portalViewData(Request $request): array
    {
        $portalContext = str_starts_with((string) $request->route()?->getName(), 'employee.')
            ? 'employee'
            : 'admin';

        return [
            'portalContext' => $portalContext,
            'routeBase' => $portalContext,
            'layout' => $portalContext === 'employee' ? 'layouts.app' : 'voyager::master',
            'hideNavbar' => $portalContext === 'employee',
            'bodyClass' => $portalContext === 'employee' ? 'employee-shell' : '',
        ];
    }
}
