<?php

namespace App\Http\Controllers\Api\V1\Email;

use App\Domain\Email\Models\MailAccount;
use App\Domain\Email\Models\MailAlias;
use App\Domain\Email\Models\MailDistributionList;
use App\Domain\Email\Services\AliasService;
use App\Domain\Email\Services\DistributionListService;
use App\Domain\Email\Services\MailboxProvisioningService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailApiController extends Controller
{
    public function __construct(
        private readonly MailboxProvisioningService $provisioning,
        private readonly DistributionListService $lists,
        private readonly AliasService $aliases,
    ) {}

    public function accounts(Request $request): JsonResponse
    {
        $accounts = MailAccount::query()
            ->when($request->identity_type, fn ($q) => $q->where('identity_type', $request->identity_type))
            ->latest('id')
            ->paginate(50);

        return response()->json($accounts);
    }

    public function show(MailAccount $account): JsonResponse
    {
        $account->load(['aliases', 'mailbox', 'domain']);

        return response()->json($account);
    }

    public function disable(MailAccount $account): JsonResponse
    {
        $this->provisioning->disable($account);

        return response()->json(['ok' => true, 'status' => $account->fresh()->status]);
    }

    public function enable(MailAccount $account): JsonResponse
    {
        $this->provisioning->enable($account);

        return response()->json(['ok' => true, 'status' => $account->fresh()->status]);
    }

    public function resetPassword(MailAccount $account): JsonResponse
    {
        $password = $this->provisioning->resetPassword($account);

        return response()->json(['ok' => true, 'password' => $password]);
    }

    public function updateQuota(Request $request, MailAccount $account): JsonResponse
    {
        $data = $request->validate(['quota_mb' => 'required|integer|min:100|max:102400']);
        $this->provisioning->changeQuota($account, (int) $data['quota_mb']);

        return response()->json(['ok' => true, 'quota_mb' => $account->fresh()->quota_mb]);
    }

    public function storeAlias(Request $request, MailAccount $account): JsonResponse
    {
        $data = $request->validate([
            'source_email' => 'required|email|max:255',
            'type' => 'nullable|in:user,functional,legacy',
        ]);

        $alias = $this->aliases->createForAccount($account, $data['source_email'], $data['type'] ?? 'user');

        return response()->json(['ok' => true, 'alias' => $alias], 201);
    }

    public function destroyAlias(MailAlias $alias): JsonResponse
    {
        $this->aliases->remove($alias);

        return response()->json(['ok' => true]);
    }

    public function lists(): JsonResponse
    {
        return response()->json(
            MailDistributionList::query()->withCount('members')->orderBy('address')->paginate(50)
        );
    }

    public function storeList(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address' => 'required|email|max:255',
            'name' => 'required|string|max:255',
            'identity_type' => 'nullable|in:student,doctor,employee,admin',
            'college_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'auto_sync' => 'nullable|boolean',
        ]);

        $rule = array_filter([
            'identity_type' => $data['identity_type'] ?? null,
            'college_id' => $data['college_id'] ?? null,
            'section_id' => $data['section_id'] ?? null,
        ]);

        $list = $this->lists->create(
            $data['address'],
            $data['name'],
            $rule ?: null,
            (bool) ($data['auto_sync'] ?? ! empty($rule))
        );

        if ($rule) {
            $this->lists->sync($list);
        }

        return response()->json(['ok' => true, 'list' => $list->fresh('members')], 201);
    }

    public function syncList(MailDistributionList $list): JsonResponse
    {
        $count = $this->lists->sync($list);

        return response()->json(['ok' => true, 'members' => $count]);
    }

    public function health(): JsonResponse
    {
        return response()->json($this->provisioning->healthCheck());
    }
}
