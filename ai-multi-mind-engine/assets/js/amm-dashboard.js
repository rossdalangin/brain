document.addEventListener('DOMContentLoaded', function() {
    const apiRoot = ammData.apiRoot;
    const nonce = ammData.nonce;

    // Tab Logic
    document.querySelectorAll('.amm-nav-item').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.amm-nav-item').forEach(i => i.classList.remove('active'));
            document.querySelectorAll('.amm-tab-content').forEach(c => c.classList.remove('active'));
            item.classList.add('active');
            document.getElementById('tab-' + item.dataset.tab).classList.add('active');
        });
    });

    // Onboarding Logic
    window.closeOnboarding = function() {
        document.getElementById('amm-onboarding-overlay').style.display = 'none';
        localStorage.setItem('amm_onboarded', 'yes');
    };
    if (!localStorage.getItem('amm_onboarded')) {
        document.getElementById('amm-onboarding-overlay').style.display = 'flex';
    }

    // Theme Logic
    document.getElementById('amm-theme-toggle').addEventListener('click', () => {
        document.getElementById('amm-dashboard-root').classList.toggle('dark-mode');
    });

    // Template Logic
    document.getElementById('amm-template-select').addEventListener('change', (e) => {
        if(e.target.value) document.getElementById('amm-input').value = e.target.value;
    });

    // Fetch Data
    function initApp() {
        // User Stats
        fetch(apiRoot + '/user', { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json()).then(data => {
                const used = data.usage.used;
                const limit = data.usage.limit;
                const pct = Math.min(100, (used / limit) * 100);

                document.getElementById('amm-user-profile').innerText = `Welcome, ${data.user_name}${data.settings.company_name ? ' @ ' + data.settings.company_name : ''}`;

                // Apply Branding
                if (data.team_branding) {
                    if (data.team_branding.logo) {
                        document.querySelector('.amm-logo').innerHTML = `<img src="${data.team_branding.logo}" style="max-width:100%; max-height:40px;">`;
                    }
                    if (data.team_branding.color) {
                        document.documentElement.style.setProperty('--amm-primary', data.team_branding.color);
                    }
                }

                document.getElementById('amm-user-stats-sidebar').innerHTML = `<strong>${data.plan.toUpperCase()}</strong><br>${used}/${limit} credits`;
                document.getElementById('amm-usage-bar').style.width = pct + '%';

                if (data.plan === 'pro' || data.plan === 'agency') {
                    const builder = document.getElementById('amm-builder-container');
                    if(builder) builder.style.display = 'block';
                }
                if (data.plan === 'agency') {
                    const branding = document.getElementById('amm-branding-container');
                    if(branding) branding.style.display = 'block';
                }
                if (data.plan !== 'free') {
                    const portal = document.getElementById('amm-portal-container');
                    if(portal) portal.style.display = 'block';
                }

                // Populate Settings
                document.getElementById('amm-set-webhook').value = data.settings.webhook_url || '';
                document.getElementById('amm-set-company').value = data.settings.company_name || '';
                document.getElementById('amm-set-default-mind').value = data.settings.default_mind || 'ceo';
                document.getElementById('amm-set-kb').value = data.settings.knowledge_base || '';
                document.getElementById('amm-set-alerts').checked = data.settings.usage_alerts;

                // Recent Activity
                const recent = document.getElementById('amm-recent-activity');
                fetch(apiRoot + '/outputs', { headers: { 'X-WP-Nonce': nonce } })
                    .then(res => res.json()).then(outputs => {
                        recent.innerHTML = outputs.slice(0, 5).map(o => `<div style="padding:10px; border-bottom:1px solid #eee;"><strong>${o.title}</strong> - ${o.date}</div>`).join('') || 'No recent activity.';
                    });

                // Populate Insights
                if (document.getElementById('amm-insight-gens')) {
                    const gens = data.insights.total_generations;
                    document.getElementById('amm-insight-gens').innerText = gens;
                    document.getElementById('amm-insight-gens-bar').style.width = Math.min(100, (gens / 50) * 100) + '%';
                    document.getElementById('amm-insight-refs').innerText = data.insights.referral_count;
                }
            });

        // Templates
        fetch(apiRoot + '/templates', { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json()).then(templates => {
                if(templates.length) {
                    const select = document.getElementById('amm-template-select');
                    select.innerHTML += templates.map(t => `<option value="${t.content}" ${t.locked ? 'disabled' : ''}>${t.title}${t.locked ? ' (Locked)' : ''}</option>`).join('');

							document.getElementById('amm-templates-grid').innerHTML = templates.map(t => `
								<div class="amm-plan-card">
									<div style="font-size:32px; margin-bottom:10px;">📜</div>
									<strong>${t.title}</strong>
									<p style="font-size:10px; color:#888;">Min Plan: ${t.min_plan}</p>
									<button class="amm-secondary-btn" onclick="document.getElementById('amm-template-select').value='${t.content}'; document.getElementById('amm-input').value='${t.content}'; document.querySelector('[data-tab=generate]').click();" ${t.locked ? 'disabled' : ''}>Use Template</button>
									${t.locked ? `<button class="amm-primary-btn" style="margin-top:10px; font-size:10px;" onclick="document.querySelector('[data-tab=billing]').click()">Upgrade to Unlock</button>` : ''}
								</div>
							`).join('');
                }
            });

        // Minds
        fetch(apiRoot + '/minds', { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json()).then(minds => {
                window.ammAllMinds = minds;
                const options = minds.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
                document.getElementById('amm-mind-select').innerHTML = options;
                document.getElementById('amm-set-default-mind').innerHTML = options;
                document.querySelectorAll('.amm-council-select').forEach(s => s.innerHTML += options);

                const categories = [...new Set(minds.map(m => m.category).filter(Boolean))];
                document.getElementById('amm-category-filter').innerHTML += categories.map(c => `<option value="${c}">${c}</option>`).join('');

                renderMindGrid(minds);
            });

        function renderMindGrid(minds) {
            document.getElementById('amm-marketplace-grid').innerHTML = minds.sort((a,b) => (b.featured||0) - (a.featured||0)).map(m => `
                <div class="amm-plan-card ${m.premium ? 'premium' : ''} ${m.featured ? 'featured' : ''}">
                    <div style="font-size:32px; margin-bottom:10px;">🧠</div>
                    <strong>${m.name}</strong>
                    <p style="font-size:10px; color:#888;">${m.category || 'Core'}</p>
                    <p>${m.premium ? 'Premium Mind' : 'Core Mind'}</p>
                    <button class="amm-secondary-btn" onclick="document.getElementById('amm-mind-select').value='${m.id}'; document.querySelector('[data-tab=generate]').click();" ${m.premium && !m.purchased ? 'disabled' : ''}>Use Mind</button>
                    ${m.premium && !m.purchased ? `<button class="amm-primary-btn" style="margin-top:10px; font-size:10px;" onclick="ammCheckout('mind_${m.id}')">Unlock for $49</button>` : ''}
                </div>
            `).join('');
        }

        document.getElementById('amm-category-filter').addEventListener('change', () => applyFilters());
        document.getElementById('amm-mind-filter').addEventListener('change', () => applyFilters());

        function applyFilters() {
            const cat = document.getElementById('amm-category-filter').value;
            const type = document.getElementById('amm-mind-filter').value;

            let filtered = window.ammAllMinds;

            if (cat) filtered = filtered.filter(m => m.category === cat);

            if (type === 'purchased') {
                filtered = filtered.filter(m => !m.premium || m.purchased);
            } else if (type === 'premium') {
                filtered = filtered.filter(m => m.premium && !m.purchased);
            }

            renderMindGrid(filtered);
        }

        // Folders
        fetch(apiRoot + '/folders', { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json()).then(folders => {
                document.getElementById('amm-workspace-folder-filter').innerHTML += folders.map(f => `<option value="${f.name}">${f.name}</option>`).join('');
            });

        // Workspace
        window.refreshWorkspace = function() {
            fetch(apiRoot + '/outputs', { headers: { 'X-WP-Nonce': nonce } })
                .then(res => res.json()).then(outputs => {
                    const workspaceList = document.getElementById('amm-workspace-list');
                    if (!outputs.length) {
                        workspaceList.innerHTML = 'No outputs saved.';
                        return;
                    }
                    workspaceList.innerHTML = '<table style="width:100%; text-align:left;">' +
                        '<tr><th><input type="checkbox" id="amm-select-all"></th><th>Date</th><th>Title</th><th>Folders</th><th>Actions</th></tr>' +
                        outputs.map(o => `<tr data-folders="${o.folders.join(',')}"><td><input type="checkbox" class="amm-out-check" value="${o.id}"></td><td>${o.date}</td><td>${o.title}</td><td>${o.folders.join(', ') || '-'}</td><td><button class="amm-secondary-btn" onclick="alert(${JSON.stringify(o.content)})">View</button> <button class="amm-secondary-btn" onclick="ammDuplicate(${o.id})">👯 Duplicate</button> <button class="amm-secondary-btn" onclick="ammShare(${o.id})">🔗 Share</button> <button class="amm-secondary-btn" onclick="ammFeedback(${o.id}, 'up')">👍</button><button class="amm-secondary-btn" onclick="ammFeedback(${o.id}, 'down')">👎</button></td></tr>`).join('') +
                        '</table>';

                    document.getElementById('amm-select-all').addEventListener('change', (e) => {
                        document.querySelectorAll('.amm-out-check').forEach(c => c.checked = e.target.checked);
                    });
                });
        }
        refreshWorkspace();

        // Team & Invites
        fetch(apiRoot + '/teams', { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json()).then(teams => {
                if(teams.length) {
                    window.ammActiveTeamId = teams[0].id;
                    document.getElementById('amm-team-list').innerHTML = teams.map(t => `<div><strong>${t.team_name}</strong> (${t.role}) ${t.role === 'admin' ? '' : `<button class="amm-secondary-btn" onclick="ammRemoveMember(${t.user_id}, ${t.id})" style="background:#ff4444; color:#fff;">Remove</button>`}</div>`).join('');

                    // Fetch Invites
                    fetch(apiRoot + '/pending-invites?team_id=' + teams[0].id, { headers: { 'X-WP-Nonce': nonce } })
                        .then(res => res.json()).then(invites => {
                            if(invites.length) {
                                document.getElementById('amm-invite-list').innerHTML = invites.map(i => `<div>${i.email} <button class="amm-secondary-btn" onclick="ammRevokeInvite(${i.id})" style="background:#ff4444; color:#fff;">Revoke</button></div>`).join('');
                            }
                        });

                    // Fetch Team Activity
                    fetch(apiRoot + '/team-activity?team_id=' + teams[0].id, { headers: { 'X-WP-Nonce': nonce } })
                        .then(res => res.json()).then(activity => {
                            if(activity.length) {
                                document.getElementById('amm-team-activity').innerHTML = activity.map(a => `<div><strong>${a.user}</strong> generated <em>${a.title}</em></div>`).join('');
                            } else {
                                document.getElementById('amm-team-activity').innerHTML = 'No recent team activity.';
                            }
                        });
                }
            });

        // Plans
        fetch(apiRoot + '/plans', { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json()).then(plans => {
                document.getElementById('amm-plans-grid').innerHTML = plans.map(p => `
                    <div class="amm-plan-card ${p.featured ? 'featured' : ''}">
                        <h4>${p.name}</h4>
                        <p>${p.price}/mo</p>
                        <p style="font-size:12px; color:#888;">${p.credits} Credits / mo</p>
                        <button onclick="ammCheckout('${p.id}')" class="amm-primary-btn">Select</button>
                    </div>
                `).join('');
            });

        // Billing History
        fetch(apiRoot + '/billing-history', { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json()).then(history => {
                const historyBox = document.getElementById('amm-billing-history');
                if (history.length === 0) {
                    historyBox.innerHTML = 'No payment history found.';
                    return;
                }
                historyBox.innerHTML = '<table style="width:100%">' +
                    '<tr><th>Date</th><th>Plan</th><th>Gateway</th><th>Status</th></tr>' +
                    history.map(h => `<tr><td>${h.created_at}</td><td>${h.plan_id.toUpperCase()}</td><td>${h.gateway}</td><td>${h.status}</td></tr>`).join('') +
                    '</table>';
            });

        // Affiliate
        fetch(apiRoot + '/affiliate', { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json()).then(data => {
                let html = `<p>Referral Link: <input type="text" value="${data.link}" readonly style="width:100%;"></p>
                    <p>Earnings: $${data.commissions}</p>
                    <h4>Your Referrals</h4>`;

                if(data.referrals && data.referrals.length) {
                    html += '<table style="width:100%">' + data.referrals.map(r => `<tr><td>${r.user_email}</td><td>${r.status}</td><td>$${r.commission_amount}</td></tr>`).join('') + '</table>';
                } else {
                    html += '<p>No referrals yet. Share your link to start earning!</p>';
                }
                document.getElementById('amm-affiliate-info').innerHTML = html;
            });
    }
    initApp();

    // Generate Logic (Typewriter Effect)
    const btn = document.getElementById('amm-generate-btn');
    const outputBox = document.getElementById('amm-output');
    window.ammChatHistory = [];

    // Load History
    fetch(apiRoot + '/get-history', { headers: { 'X-WP-Nonce': nonce } })
        .then(res => res.json()).then(h => { window.ammChatHistory = h; });

    btn.addEventListener('click', () => {
        const userInput = document.getElementById('amm-input').value;
        if(!userInput) return;

        btn.disabled = true;
        outputBox.innerText = "The mind is thinking...";

        fetch(apiRoot + '/generate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({
                mind_id: document.getElementById('amm-mind-select').value,
                output_type: document.getElementById('amm-type-select').value,
                user_input: userInput,
                language: document.getElementById('amm-language-select').value,
                history: window.ammChatHistory
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                outputBox.innerText = "";
                let i = 0;
                const text = data.content;
                const interval = setInterval(() => {
                    outputBox.innerText += text[i];
                    i++;
                    if(i >= text.length) {
                        clearInterval(interval);
                        btn.disabled = false;
                        document.getElementById('amm-export-btn').style.display = 'block';
                        document.getElementById('amm-export-html-btn').style.display = 'block';
                        window.ammChatHistory.push({ role: 'user', content: userInput });
                        window.ammChatHistory.push({ role: 'assistant', content: text });

								// Save History
								fetch(apiRoot + '/save-history', {
									method: 'POST',
									headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
									body: JSON.stringify({ history: window.ammChatHistory })
								});

								outputBox.innerHTML += '<div style="margin-top:20px; font-size:12px; color:green; font-weight:bold;">✅ Strategy saved and persistent memory updated.</div>';
                    }
                }, 5);
            } else {
                outputBox.innerText = "Error: " + data.message;
                btn.disabled = false;
            }
        });
    });

    // Feedback Logic
    window.ammFeedback = function(id, rating) {
        fetch(apiRoot + '/feedback', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({ post_id: id, rating: rating })
        }).then(res => res.json()).then(data => { if(data.success) alert('Feedback recorded. Thank you!'); });
    };

    // Share Logic
    window.ammShare = function(id) {
        fetch(apiRoot + '/share', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({ post_id: id })
        })
        .then(res => res.json())
        .then(data => {
            if(data.is_public) {
                prompt('Public link copied to clipboard (Ctrl+C):', data.share_url);
            } else {
                alert('Intelligence marked as private.');
            }
        });
    };

    // Handle Folder Filter
    document.getElementById('amm-workspace-folder-filter').addEventListener('change', (e) => {
        const folder = e.target.value;
        document.querySelectorAll('#amm-workspace-list tr').forEach(tr => {
            if (tr.querySelector('th')) return;
            const folders = tr.dataset.folders || '';
            tr.style.display = (!folder || folders.includes(folder)) ? '' : 'none';
        });
    });

    // Handle Search
    document.getElementById('amm-workspace-search').addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('#amm-workspace-list tr').forEach(tr => {
            if (tr.querySelector('th')) return;
            const title = tr.innerText.toLowerCase();
            tr.style.display = title.includes(term) ? '' : 'none';
        });
    });

    // Duplicate Logic
    window.ammDuplicate = function(id) {
        fetch(apiRoot + '/duplicate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({ post_id: id })
        }).then(res => res.json()).then(data => { if(data.success) refreshWorkspace(); });
    };

    // Portal Logic
    window.ammPortal = function() {
        fetch(apiRoot + '/billing-portal', { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json()).then(data => { if(data.url) window.location.href = data.url; });
    };

    // Quick Ignite
    window.quickIgnite = function(mindId) {
        document.getElementById('amm-mind-select').value = mindId;
        document.querySelector('[data-tab=generate]').click();
    };

    // Export PDF
    document.getElementById('amm-export-btn').addEventListener('click', () => {
        const win = window.open('', '_blank');
        win.document.write(`<html><body style="font-family:sans-serif; padding:50px;"><h1>AI Multi-Mind Output</h1><hr>${outputBox.innerText}</body></html>`);
        win.print();
    });

    // Export HTML
    document.getElementById('amm-export-html-btn').addEventListener('click', () => {
        const blob = new Blob([outputBox.innerText], { type: 'text/html' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'ai-strategy.html';
        a.click();
    });

    // Handle Bulk Delete
    document.getElementById('amm-bulk-delete-btn').addEventListener('click', () => {
        const ids = Array.from(document.querySelectorAll('.amm-out-check:checked')).map(c => c.value);
        if(ids.length === 0 || !confirm('Delete selected?')) return;

        fetch(apiRoot + '/bulk-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({ ids, action: 'delete' })
        }).then(res => res.json()).then(data => { if(data.success) refreshWorkspace(); });
    });

    // Handle New Folder
    document.getElementById('amm-new-folder-btn').addEventListener('click', () => {
        const name = prompt('Enter folder name:');
        if(!name) return;

        fetch(apiRoot + '/create-folder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({ name })
        }).then(res => res.json()).then(data => {
            if(data.success) {
                alert('Folder Created!');
                refreshWorkspace();
            }
        });
    });

    // Handle Refine Prompt
    document.getElementById('amm-refine-btn').addEventListener('click', () => {
        const input = document.getElementById('amm-input');
        if(!input.value) return;

        document.getElementById('amm-refine-btn').innerText = 'Magic in progress...';
        fetch(apiRoot + '/refine-prompt', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({ user_input: input.value })
        })
        .then(res => res.json())
        .then(data => {
            if(data.refined_prompt) {
                input.value = data.refined_prompt;
            }
        })
        .finally(() => {
            document.getElementById('amm-refine-btn').innerText = '✨ Refine with Magic BFF';
        });
    });

    // Handle Create Template
    if (document.getElementById('amm-create-template-btn')) {
        document.getElementById('amm-create-template-btn').addEventListener('click', () => {
            fetch(apiRoot + '/create-template', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({
                    title: document.getElementById('amm-new-template-title').value,
                    content: document.getElementById('amm-new-template-content').value
                })
            }).then(res => res.json()).then(data => { if(data.success) { alert('Template Created!'); location.reload(); } });
        });
    }

    // Handle Branding
    document.getElementById('amm-branding-btn').addEventListener('click', () => {
        fetch(apiRoot + '/update-branding', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({
                team_id: window.ammActiveTeamId,
                logo: document.getElementById('amm-branding-logo').value,
                color: document.getElementById('amm-branding-color').value
            })
        }).then(res => res.json()).then(data => { if(data.success) alert('Branding Updated!'); });
    });

    // Handle Remove Member
    window.ammRemoveMember = function(userId, teamId) {
        if(!confirm('Remove this member?')) return;
        fetch(apiRoot + '/remove-member', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({ user_id: userId, team_id: teamId })
        }).then(res => res.json()).then(data => { if(data.success) location.reload(); });
    };

    // Handle Revoke Invite
    window.ammRevokeInvite = function(id) {
        fetch(apiRoot + '/revoke-invite', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({ id })
        }).then(res => res.json()).then(data => { if(data.success) location.reload(); });
    };

    // Handle Invite
    document.getElementById('amm-invite-btn').addEventListener('click', () => {
        const email = document.getElementById('amm-invite-email').value;
        if(!email) return;

        fetch(apiRoot + '/invite', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({ team_id: window.ammActiveTeamId, email })
        }).then(res => res.json()).then(data => {
            if(data.success) {
                prompt('Invite link generated! Send this to your team member:', data.invite_url);
            }
        });
    });

    // Handle Council
    document.getElementById('amm-ignite-council-btn').addEventListener('click', () => {
        const mind_ids = Array.from(document.querySelectorAll('.amm-council-select')).map(s => s.value).filter(Boolean);
        const user_input = document.getElementById('amm-council-input').value;
        const mode = document.querySelector('input[name="council-mode"]:checked').value;
        const output = document.getElementById('amm-council-output');

        output.innerText = 'The Council is deliberating...';
        fetch(apiRoot + '/collaborate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({ mind_ids, user_input, mode })
        }).then(res => res.json()).then(data => {
            if(data.success) {
                let html = `<h3>Council Deliberation Complete (${mode.toUpperCase()})</h3>`;
                if (data.sequence && data.sequence.length) {
                    html += '<div style="margin-bottom:20px; border-left:4px solid #007cba; padding-left:20px;">';
                    data.sequence.forEach((step, idx) => {
                        html += `<details style="margin-bottom:10px;"><summary style="cursor:pointer; font-weight:bold; color:#007cba;">Mind ${idx+1} Reflection</summary><div style="padding:10px; background:#f0f8ff; border-radius:8px; font-size:13px; margin-top:5px;">${typeof step === 'string' ? step : step.content}</div></details>`;
                    });
                    html += '</div>';
                }
                html += `<h4>Final Strategy Output</h4><div>${data.final_output}</div>`;
                output.innerHTML = html;
            }
        });
    });

    // Handle Save Settings
    document.getElementById('amm-save-settings-btn').addEventListener('click', () => {
        fetch(apiRoot + '/update-settings', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({
                webhook_url: document.getElementById('amm-set-webhook').value,
                company_name: document.getElementById('amm-set-company').value,
                default_mind: document.getElementById('amm-set-default-mind').value,
                knowledge_base: document.getElementById('amm-set-kb').value,
                usage_alerts: document.getElementById('amm-set-alerts').checked
            })
        }).then(res => res.json()).then(data => { if(data.success) { alert('Settings Saved!'); location.reload(); } });
    });

    window.ammCheckout = function(planId) {
        fetch(apiRoot + '/checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({ plan_id: planId, gateway: 'stripe' })
        }).then(res => res.json()).then(data => { if(data.url) window.location.href = data.url; });
    };
});
