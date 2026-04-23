<style>
    .voyager .custom-admin-page--voyager{
        --primary: #f4c21d;
        --primary-strong: #d6a20a;
        --secondary: #0d5c86;
        --secondary-dark: #083b59;
        --ink: #183245;
        --custom-admin-border: rgba(13,92,134,.10);
        --custom-admin-shadow: 0 12px 28px rgba(17,54,79,.07);
        color:var(--ink);
    }

    .voyager .custom-admin-page--voyager.employee-cycle-page{
        display:flex;
        flex-direction:column;
        gap:24px;
        padding:6px 0 12px;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-header,
    .voyager .custom-admin-page--voyager .employee-management-header{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:16px;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-title{
        margin-bottom:6px;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-subtitle{
        margin:0;
        color:#5a7080;
        line-height:1.85;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-header-link{
        align-self:center;
        white-space:nowrap;
    }

    .voyager .custom-admin-page--voyager .employee-management-panel,
    .voyager .custom-admin-page--voyager .employee-cycle-table{
        background:#fff;
        border-radius:16px;
        border:1px solid var(--custom-admin-border);
        box-shadow:var(--custom-admin-shadow);
    }

    .voyager .custom-admin-page--voyager .employee-cycle-create-panel,
    .voyager .custom-admin-page--voyager .employee-cycle-table-panel{
        overflow:hidden;
    }

    .voyager .custom-admin-page--voyager .employee-management-form-panel,
    .voyager .custom-admin-page--voyager .employee-cycle-table-panel .panel-body{
        padding:22px;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-section-title{
        margin:0 0 18px;
        color:var(--secondary-dark);
        font-size:1.35rem;
        font-weight:800;
    }

    .voyager .custom-admin-page--voyager .employee-form-grid > div{
        margin-bottom:14px;
    }

    .voyager .custom-admin-page--voyager .employee-form-grid label{
        display:block;
        margin-bottom:8px;
        color:var(--secondary-dark);
        font-weight:700;
    }

    .voyager .custom-admin-page--voyager .employee-form-grid .form-control{
        min-height:46px;
        border-radius:14px;
        border:1px solid rgba(13,92,134,.16);
        box-shadow:none;
        transition:border-color .2s ease, box-shadow .2s ease;
    }

    .voyager .custom-admin-page--voyager .employee-form-grid .form-control:focus{
        border-color:rgba(13,92,134,.42);
        box-shadow:0 0 0 4px rgba(13,92,134,.08);
    }

    .voyager .custom-admin-page--voyager .employee-form-actions{
        margin-top:16px;
        display:flex;
        justify-content:flex-start;
    }

    .voyager .custom-admin-page--voyager .employee-inline-form{
        margin:0;
    }

    .voyager .custom-admin-page--voyager .employee-action-btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        min-height:42px;
        padding:10px 18px;
        border:none;
        border-radius:14px;
        font-weight:800;
        text-decoration:none;
        transition:transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
        box-shadow:0 10px 22px rgba(13,92,134,.10);
        cursor:pointer;
        line-height:1.2;
        appearance:none;
    }

    .voyager .custom-admin-page--voyager .employee-action-btn:hover,
    .voyager .custom-admin-page--voyager .employee-action-btn:focus{
        text-decoration:none;
        transform:translateY(-1px);
    }

    .voyager .custom-admin-page--voyager .employee-action-btn--submit{
        margin-top:8px;
    }

    .voyager .custom-admin-page--voyager .employee-action-btn--sm{
        min-height:38px;
        padding:8px 14px;
        font-size:.95rem;
    }

    .voyager .custom-admin-page--voyager .employee-action-btn--primary{
        background:linear-gradient(135deg, #ffd84d 0%, var(--primary) 55%, var(--primary-strong) 100%);
        color:var(--secondary-dark);
    }

    .voyager .custom-admin-page--voyager .employee-action-btn--primary:hover,
    .voyager .custom-admin-page--voyager .employee-action-btn--primary:focus{
        color:var(--secondary-dark);
        box-shadow:0 14px 28px rgba(244,194,29,.24);
    }

    .voyager .custom-admin-page--voyager .employee-action-btn--success{
        background:linear-gradient(135deg, #dff7ea 0%, #c3efd9 100%);
        color:#0f6a45;
        border:1px solid rgba(15,106,69,.10);
    }

    .voyager .custom-admin-page--voyager .employee-action-btn--success:hover,
    .voyager .custom-admin-page--voyager .employee-action-btn--success:focus{
        color:#0f6a45;
        box-shadow:0 12px 24px rgba(15,106,69,.16);
    }

    .voyager .custom-admin-page--voyager .employee-action-btn--warning{
        background:linear-gradient(135deg, #fff2c8 0%, #ffd86a 100%);
        color:#8a5a00;
        border:1px solid rgba(154,108,0,.14);
    }

    .voyager .custom-admin-page--voyager .employee-action-btn--warning:hover,
    .voyager .custom-admin-page--voyager .employee-action-btn--warning:focus{
        color:#704900;
        box-shadow:0 12px 24px rgba(244,194,29,.20);
    }

    .voyager .custom-admin-page--voyager .employee-action-btn--danger{
        background:linear-gradient(135deg, #ffebe8 0%, #ffd9d3 100%);
        color:#b23b30;
        border:1px solid rgba(178,59,48,.16);
    }

    .voyager .custom-admin-page--voyager .employee-action-btn--danger:hover,
    .voyager .custom-admin-page--voyager .employee-action-btn--danger:focus{
        color:#932f26;
        box-shadow:0 12px 24px rgba(178,59,48,.16);
    }

    .voyager .custom-admin-page--voyager .employee-action-btn--neutral{
        background:#fff;
        color:var(--secondary-dark);
        border:1px solid rgba(13,92,134,.14);
    }

    .voyager .custom-admin-page--voyager .employee-action-btn--neutral:hover,
    .voyager .custom-admin-page--voyager .employee-action-btn--neutral:focus{
        color:var(--secondary-dark);
        box-shadow:0 12px 24px rgba(13,92,134,.12);
    }

    .voyager .custom-admin-page--voyager .employee-cycle-table-wrap{
        overflow-x:auto;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-table{
        margin:0;
        overflow:hidden;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-table thead th{
        background:linear-gradient(135deg, rgba(13,92,134,.08) 0%, rgba(244,194,29,.16) 100%);
        color:var(--secondary-dark);
        border-bottom:1px solid rgba(13,92,134,.12);
        font-weight:800;
        padding:16px 14px;
        vertical-align:middle;
        white-space:nowrap;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-table tbody td{
        padding:16px 14px;
        vertical-align:middle;
        border-color:rgba(13,92,134,.08);
        color:#344955;
        white-space:normal;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-table tbody tr:hover{
        background:rgba(13,92,134,.02);
    }

    .voyager .custom-admin-page--voyager .employee-cycle-table tbody td:last-child{
        min-width:240px;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-status{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:34px;
        padding:6px 12px;
        border-radius:999px;
        font-size:.88rem;
        font-weight:800;
        white-space:nowrap;
        text-transform:none;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-status--draft{
        background:#eef4f7;
        color:#4c6475;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-status--open,
    .voyager .custom-admin-page--voyager .employee-cycle-status--approved{
        background:#e7f7ef;
        color:#0f6a45;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-status--closed{
        background:#fff4df;
        color:#8f6200;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-status--cancelled{
        background:#ffebe8;
        color:#b23b30;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-actions{
        display:flex;
        flex-wrap:wrap;
        gap:8px 10px;
        align-items:center;
        min-width:220px;
    }

    .voyager .custom-admin-page--voyager .employee-cycle-actions .employee-inline-form{
        display:inline-flex;
    }

    .voyager .custom-admin-page--voyager .alert{
        border-radius:14px;
    }

    @media (max-width:768px){
        .voyager .custom-admin-page--voyager .employee-cycle-header,
        .voyager .custom-admin-page--voyager .employee-management-header{
            flex-direction:column;
            align-items:flex-start;
        }

        .voyager .custom-admin-page--voyager .employee-management-form-panel,
        .voyager .custom-admin-page--voyager .employee-cycle-table-panel .panel-body{
            padding:16px;
        }

        .voyager .custom-admin-page--voyager .employee-cycle-header-link,
        .voyager .custom-admin-page--voyager .employee-cycle-actions .employee-action-btn{
            width:100%;
            justify-content:center;
        }

        .voyager .custom-admin-page--voyager .employee-cycle-table tbody td:last-child{
            min-width:0;
        }
    }
</style>
