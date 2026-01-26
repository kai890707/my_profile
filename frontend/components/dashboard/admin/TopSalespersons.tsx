import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Trophy, Eye, Phone } from 'lucide-react';
import type { Salesperson } from '@/types/dashboard';

export interface TopSalespersonsProps {
  salespersons: Salesperson[];
  onSalespersonClick?: (salespersonId: number) => void;
  isLoading?: boolean;
}

export function TopSalespersons({
  salespersons,
  onSalespersonClick,
  isLoading,
}: TopSalespersonsProps) {
  if (isLoading) {
    return (
      <Card>
        <CardHeader>
          <Skeleton className="h-6 w-40" />
        </CardHeader>
        <CardContent>
          {[...Array(5)].map((_, i) => (
            <Skeleton key={i} className="h-16 w-full mb-3" />
          ))}
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Trophy className="h-5 w-5 text-amber-500" />
          熱門業務員 Top 10
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-3">
          {salespersons.map((salesperson, index) => (
            <div
              key={salesperson.id}
              className="flex items-center gap-4 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer"
              onClick={() => onSalespersonClick?.(salesperson.id)}
            >
              {/* Ranking */}
              <div
                className={`flex items-center justify-center w-8 h-8 rounded-full font-bold ${
                  index === 0
                    ? 'bg-amber-100 text-amber-600'
                    : index === 1
                    ? 'bg-slate-100 text-slate-600'
                    : index === 2
                    ? 'bg-orange-100 text-orange-600'
                    : 'bg-slate-50 text-slate-500'
                }`}
              >
                {index + 1}
              </div>

              {/* Name */}
              <div className="flex-1">
                <p className="font-semibold text-slate-900">
                  {salesperson.name}
                </p>
                <div className="flex items-center gap-3 mt-1">
                  <span className="flex items-center gap-1 text-xs text-slate-500">
                    <Eye className="h-3 w-3" />
                    {salesperson.profile_views.toLocaleString()}
                  </span>
                  <span className="flex items-center gap-1 text-xs text-slate-500">
                    <Phone className="h-3 w-3" />
                    {salesperson.contact_requests}
                  </span>
                </div>
              </div>

              {/* Conversion Rate */}
              <Badge variant="success">
                {salesperson.conversion_rate.toFixed(1)}%
              </Badge>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
