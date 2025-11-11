// Progress data structure with trend and ETA support
export const progressData = [
  { 
    id: 'thm', 
    label: 'TryHackMe', 
    unit: 'rooms', 
    value: 74, 
    goal: 200, 
    link: 'https://tryhackme.com/p/sabstha98?tab=completed-rooms',
    trend: { amount: 12, window: '30d' },
    eta: 'Jan 2026'
  },
  { 
    id: 'udemy', 
    label: 'Udemy', 
    unit: 'hours', 
    value: 120, 
    goal: 250,
    trend: { amount: 15, window: '30d' },
    eta: 'Mar 2026'
  },
  { 
    id: 'books', 
    label: 'Books', 
    unit: 'pages', 
    value: 1050, 
    goal: 2000,
    trend: { amount: 85, window: '30d' },
    eta: 'Jun 2026'
  },
  { 
    id: 'python', 
    label: 'Python', 
    unit: 'LoC', 
    value: 4500, 
    goal: 10000, 
    link: 'https://github.com/',
    trend: { amount: 420, window: '7d' },
    eta: 'Dec 2025'
  },
  { 
    id: 'java', 
    label: 'Java', 
    unit: 'labs', 
    value: 23, 
    goal: 50,
    trend: { amount: 3, window: '30d' },
    eta: 'Feb 2026'
  },
];
