export type NotificationTemplate = {
    id: number;
    key: string;
    name: string;
    channel: string;
    subject: string | null;
    body: string | null;
    variables: string[];
    active: boolean;
    updated_at: string | null;
};

export type TemplateForm = {
    subject: string;
    body: string;
    active: boolean;
};
