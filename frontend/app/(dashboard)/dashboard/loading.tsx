import { Card, CardContent } from '@/components/ui/card';
import { ProfileSkeleton } from '@/components/ui/skeleton';

export default function DashboardLoading() {
  return (
    <div className="space-y-6">
      <div className="h-8 w-48 bg-slate-200 rounded animate-pulse" />

      <Card>
        <CardContent className="p-8">
          <ProfileSkeleton />
        </CardContent>
      </Card>
    </div>
  );
}
