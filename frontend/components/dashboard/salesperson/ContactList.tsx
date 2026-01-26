import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { formatDistanceToNow } from 'date-fns';
import { zhTW } from 'date-fns/locale';
import { EmptyState } from '@/components/dashboard/EmptyState';
import { MessageSquare } from 'lucide-react';
import type { Contact } from '@/types/dashboard';

export interface ContactListProps {
  contacts: Contact[];
  onContactClick?: (contactId: number) => void;
  isLoading?: boolean;
}

const statusConfig = {
  pending: { label: '待處理', variant: 'warning' as const },
  contacted: { label: '已聯繫', variant: 'default' as const },
  closed: { label: '已結案', variant: 'secondary' as const },
};

export function ContactList({
  contacts,
  onContactClick,
  isLoading,
}: ContactListProps) {
  if (isLoading) {
    return (
      <Card>
        <CardHeader>
          <Skeleton className="h-6 w-32" />
        </CardHeader>
        <CardContent>
          {[...Array(3)].map((_, i) => (
            <div key={i} className="mb-4">
              <Skeleton className="h-4 w-full mb-2" />
              <Skeleton className="h-3 w-3/4" />
            </div>
          ))}
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <MessageSquare className="h-5 w-5" />
          最近聯繫記錄
        </CardTitle>
      </CardHeader>
      <CardContent>
        {contacts.length === 0 ? (
          <EmptyState
            icon={<MessageSquare className="h-8 w-8 text-slate-300" />}
            title="尚無聯繫記錄"
            description="還沒有客戶聯繫您。優化您的檔案以吸引更多客戶！"
          />
        ) : (
          <div className="space-y-4">
            {contacts.map((contact) => (
              <div
                key={contact.id}
                className="p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer"
                onClick={() => onContactClick?.(contact.id)}
              >
                {/* Header */}
                <div className="flex items-center justify-between mb-2">
                  <div>
                    <p className="font-semibold text-slate-900">
                      {contact.customer_name}
                    </p>
                    <p className="text-sm text-slate-500">
                      {contact.customer_email}
                    </p>
                  </div>
                  <Badge variant={statusConfig[contact.status].variant}>
                    {statusConfig[contact.status].label}
                  </Badge>
                </div>

                {/* Message Preview */}
                <p className="text-sm text-slate-600 line-clamp-2 mb-2">
                  {contact.message}
                </p>

                {/* Time */}
                <p className="text-xs text-slate-400">
                  {formatDistanceToNow(new Date(contact.created_at), {
                    addSuffix: true,
                    locale: zhTW,
                  })}
                </p>
              </div>
            ))}
          </div>
        )}
      </CardContent>
    </Card>
  );
}
