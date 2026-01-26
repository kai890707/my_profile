import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Users, UserCheck, UserX } from 'lucide-react';
import { Skeleton } from '@/components/ui/skeleton';
import type { Activity } from '@/types/dashboard';

export interface ActivityCardProps {
  activity: Activity;
  isLoading?: boolean;
}

export function ActivityCard({ activity, isLoading }: ActivityCardProps) {
  if (isLoading) {
    return (
      <Card>
        <CardHeader>
          <Skeleton className="h-6 w-32" />
        </CardHeader>
        <CardContent>
          <Skeleton className="h-20 w-full" />
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Users className="h-5 w-5" />
          業務員活躍度
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {/* Total */}
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <Users className="h-4 w-4 text-slate-500" />
              <span className="text-sm text-slate-600">總業務員數</span>
            </div>
            <span className="text-2xl font-bold text-slate-900">
              {activity.total_salespersons}
            </span>
          </div>

          {/* Active */}
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <UserCheck className="h-4 w-4 text-teal-500" />
              <span className="text-sm text-slate-600">活躍業務員</span>
            </div>
            <span className="text-xl font-semibold text-teal-600">
              {activity.active_salespersons}
            </span>
          </div>

          {/* Inactive */}
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <UserX className="h-4 w-4 text-slate-400" />
              <span className="text-sm text-slate-600">低活躍業務員</span>
            </div>
            <span className="text-xl font-semibold text-slate-500">
              {activity.inactive_salespersons}
            </span>
          </div>

          {/* Activity Rate */}
          <div className="pt-4 border-t border-slate-200">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-slate-700">
                活躍率
              </span>
              <span className="text-2xl font-bold text-teal-600">
                {activity.activity_rate.toFixed(1)}%
              </span>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
