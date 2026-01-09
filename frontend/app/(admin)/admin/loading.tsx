import { Card, CardContent } from '@/components/ui/card';
import { ProfileSkeleton } from '@/components/ui/skeleton';

export default function AdminLoading() {
  return (
    <div className="space-y-6">
      <div className="h-8 w-48 bg-slate-200 rounded animate-pulse" />

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {[1, 2, 3, 4].map((i) => (
          <Card key={i}>
            <CardContent className="p-6">
              <div className="h-4 w-24 bg-slate-200 rounded animate-pulse mb-4" />
              <div className="h-8 w-16 bg-slate-200 rounded animate-pulse" />
            </CardContent>
          </Card>
        ))}
      </div>

      <Card>
        <CardContent className="p-8">
          <ProfileSkeleton />
        </CardContent>
      </Card>
    </div>
  );
}
